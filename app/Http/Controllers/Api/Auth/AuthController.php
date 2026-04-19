<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\SpinRuns;
use App\Models\User;
use App\Models\Wallet;
use App\Traits\Affiliates\AffiliateHistoryTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    use AffiliateHistoryTrait;

    public function __construct()
    {
        $this->middleware('auth.jwt', ['except' => ['login', 'register', 'submitForgetPassword', 'submitResetPassword']]);
    }

    public function verify()
    {
        return response()->json(auth('api')->user());
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => ['required', 'string'],
                'password' => ['required', 'string'],
            ]);

            $email = trim((string) $request->input('email'));
            $password = (string) $request->input('password');

            $user = User::where('email', $email)->first();

            if (!$user) {
                return response()->json([
                    'error' => 'User not found with this email.',
                ], 400);
            }

            if (!Hash::check($password, (string) $user->password)) {
                return response()->json([
                    'error' => 'Check credentials',
                ], 400);
            }

            $credentials = [
                'email' => $email,
                'password' => $password,
            ];

            if (!$token = auth('api')->attempt($credentials)) {
                return response()->json([
                    'error' => 'Could not create token',
                ], 400);
            }

            return $this->respondWithToken($token);
        } catch (JWTException $e) {
            Log::error('AuthController@login JWT failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'error' => 'Could not create token',
                'debug_message' => $e->getMessage(),
            ], 400);
        } catch (\Throwable $e) {
            Log::error('AuthController@login failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function register(Request $request)
    {
        try {
            $setting = \Helper::getSetting();

            $rules = [
                'name' => 'required|string',
                'email' => 'required|email|unique:users',
                'password' => ['required', 'confirmed', Rules\Password::min(6)],
                'phone' => 'required',
                'cpf' => 'required|cpf|unique:users',
                'term_a' => 'required',
                'agreement' => 'required',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $payload = $request->only(['name', 'email', 'phone']);
            $payload['password'] = Hash::make((string) $request->password);

            if ($user = User::create($payload)) {
                if (isset($request->reference_code) && !empty($request->reference_code)) {
                    $checkAffiliate = User::where('inviter_code', $request->reference_code)->first();

                    if (!empty($checkAffiliate)) {
                        $user->update(['inviter' => $checkAffiliate->id]);
                    }

                    self::saveAffiliateHistory($user);
                }

                $this->createWallet($user);

                if ($setting && $setting->disable_spin) {
                    if (!empty($request->spin_token)) {
                        try {
                            $str = base64_decode($request->spin_token);
                            $obj = json_decode($str);

                            $spinRun = SpinRuns::where([
                                'key' => $obj->signature ?? null,
                                'nonce' => $obj->nonce ?? null,
                            ])->first();

                            if ($spinRun) {
                                $data = $spinRun->prize;
                                $obj = json_decode($data);
                                $value = $obj->value ?? 0;

                                if (\Schema::hasColumn('wallets', 'balance_bonus')) {
                                    Wallet::where('user_id', $user->id)->increment('balance_bonus', $value);
                                }
                            }
                        } catch (\Exception $e) {
                            return response()->json(['error' => $e->getMessage()], 400);
                        }
                    }
                }

                $credentials = [
                    'email' => trim((string) $request->email),
                    'password' => (string) $request->password,
                ];

                $token = auth('api')->attempt($credentials);

                if (!$token) {
                    return response()->json(['error' => 'Unauthorized'], 401);
                }

                return $this->respondWithToken($token);
            }

            return response()->json([
                'error' => 'Could not create user.',
            ], 400);
        } catch (\Exception $e) {
            Log::error('AuthController@register failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    private function createWallet($user)
    {
        $setting = \Helper::getSetting();

        $data = [
            'user_id' => $user->id,
        ];

        if (\Schema::hasColumn('wallets', 'currency')) {
            $data['currency'] = $setting->currency_code ?? 'USD';
        }

        if (\Schema::hasColumn('wallets', 'symbol')) {
            $data['symbol'] = $setting->currency_symbol ?? '$';
        }

        if (\Schema::hasColumn('wallets', 'active')) {
            $data['active'] = 1;
        }

        if (\Schema::hasColumn('wallets', 'balance')) {
            $data['balance'] = 0;
        }

        if (\Schema::hasColumn('wallets', 'total_balance')) {
            $data['total_balance'] = 0;
        }

        if (\Schema::hasColumn('wallets', 'created_at')) {
            $data['created_at'] = now();
        }

        if (\Schema::hasColumn('wallets', 'updated_at')) {
            $data['updated_at'] = now();
        }

        Wallet::firstOrCreate(
            ['user_id' => $user->id],
            $data
        );
    }

    public function me()
    {
        return response()->json(auth('api')->user());
    }

    public function logout()
    {
        auth('api')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh()
    {
        return $this->respondWithToken(auth('api')->refresh());
    }

    public function submitForgetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users',
        ]);

        $token = Str::random(5);

        $psr = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!empty($psr)) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
        }

        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => $token,
            'created_at' => Carbon::now(),
        ]);

        \Mail::send(
            'emails.forget-password',
            ['token' => $token, 'resetLink' => url('/reset-password/' . $token)],
            function ($message) use ($request) {
                $message->to($request->email);
                $message->subject('Reset Password');
            }
        );

        return response()->json([
            'status' => true,
            'message' => 'We have e-mailed your password reset link!',
        ], 200);
    }

    public function submitResetPassword(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:users',
                'password' => 'required|string|min:6|confirmed',
                'password_confirmation' => 'required',
                'token' => 'required',
            ]);

            $checkToken = DB::table('password_reset_tokens')
                ->where('token', $request->token)
                ->first();

            if (!empty($checkToken)) {
                $user = User::where('email', $request->email)->first();

                if (!empty($user)) {
                    if ($user->update(['password' => Hash::make((string) $request->password)])) {
                        DB::table('password_reset_tokens')
                            ->where(['email' => $request->email])
                            ->delete();

                        return response()->json([
                            'status' => true,
                            'message' => 'Your password has been changed!',
                        ], 200);
                    }

                    return response()->json(['error' => 'Erro ao atualizar senha'], 400);
                }

                return response()->json(['error' => 'Email não é valido!'], 400);
            }

            return response()->json(['error' => 'Token não é valido!'], 400);
        } catch (\Exception $e) {
            Log::error('AuthController@submitResetPassword failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    protected function respondWithToken(string $token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => auth('api')->user(),
            'expires_in' => time() + 1,
        ]);
    }
}