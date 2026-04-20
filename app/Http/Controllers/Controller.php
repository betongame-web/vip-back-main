<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function fallbackSuccess(array $data = [], string $message = 'OK'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ]);
    }

    protected function resolveUserFromGameToken(?string $token): ?User
    {
        if (!$token) {
            return null;
        }

        try {
            $decoded = \Helper::DecToken($token);
            if (is_array($decoded) && !empty($decoded['status']) && !empty($decoded['id'])) {
                return User::with('wallet')->find($decoded['id']);
            }
        } catch (\Throwable $e) {
            Log::warning('resolveUserFromGameToken failed', ['message' => $e->getMessage()]);
        }

        return null;
    }

    protected function fallbackCredit(?User $user): float
    {
        if (!$user || !$user->wallet) {
            return 0.0;
        }

        if ((bool) ($user->is_demo_agent ?? false)) {
            return (float) ($user->wallet->balance_demo ?? 0);
        }

        return (float) ($user->wallet->total_balance ?? 0);
    }

    public function buy(Request $request, string $token): JsonResponse
    {
        if (method_exists($this, 'spin')) {
            return $this->spin($request, $token);
        }

        return $this->fallbackSuccess([
            'credit' => $this->fallbackCredit($this->resolveUserFromGameToken($token)),
            'free_num' => 0,
            'num_line' => (int) ($request->numline ?? 5),
            'bet_amount' => (float) ($request->betamount ?? 0),
            'jackpot' => 0,
            'scaler' => 0,
            'pull' => [
                'WinAmount' => 0,
                'WinOnDrop' => 0,
                'SlotIcons' => [],
                'ActiveIcons' => [],
                'ActiveLines' => [],
                'DropLine' => 0,
                'DropLineData' => [],
                'WildColumIcon' => '',
                'HasScatter' => false,
                'TotalWay' => 0,
            ],
        ], 'Buy feature fallback');
    }

    public function logs(string $token): JsonResponse
    {
        $user = $this->resolveUserFromGameToken($token);
        $items = [];

        if ($user) {
            try {
                $orders = Order::where('user_id', $user->id)->orderByDesc('id')->limit(20)->get();
                foreach ($orders as $order) {
                    $amount = (float) ($order->amount ?? 0);
                    $items[] = [
                        'spin_date' => optional($order->created_at)->format('Y-m-d') ?? now()->format('Y-m-d'),
                        'transaction' => $order->transaction_id ?? ('TX-'.Str::upper(Str::random(8))),
                        'bet_amount' => $amount,
                        'total_bet' => $amount,
                        'win_amount' => $order->type === 'win' ? $amount : 0,
                        'credit_line' => 1,
                        'profit' => $order->type === 'win' ? $amount : 0,
                        'balance' => $this->fallbackCredit($user),
                        'free_num' => 0,
                        'feature_in' => 0,
                        'multipy' => 0,
                        'icon_data' => [],
                        'active_lines' => [],
                        'total_way' => 0,
                        'drop_line' => [],
                    ];
                }
            } catch (\Throwable $e) {
                Log::warning('logs fallback failed', ['message' => $e->getMessage()]);
            }
        }

        return $this->fallbackSuccess($items, 'Logs loaded');
    }

    public function save(Request $request, string $token): JsonResponse
    {
        return $this->fallbackSuccess([
            'saved' => true,
            'has_state' => !empty($request->data),
        ], 'Session saved');
    }

    public function histories(string $token): JsonResponse
    {
        $user = $this->resolveUserFromGameToken($token);
        $items = [];

        if ($user) {
            try {
                $orders = Order::where('user_id', $user->id)->orderByDesc('id')->limit(20)->get();
                foreach ($orders as $index => $order) {
                    $amount = (float) ($order->amount ?? 0);
                    $items[] = [
                        'id' => (int) ($order->id ?? ($index + 1)),
                        'spin_date' => optional($order->created_at)->format('Y-m-d') ?? now()->format('Y-m-d'),
                        'spin_hour' => optional($order->created_at)->format('H:i:s') ?? now()->format('H:i:s'),
                        'transaction' => $order->transaction_id ?? ('TX-'.Str::upper(Str::random(8))),
                        'total_bet' => $amount,
                        'win_amount' => $order->type === 'win' ? $amount : 0,
                        'credit_line' => 1,
                        'bet_amount' => $amount,
                        'profit' => $order->type === 'win' ? $amount : 0,
                        'balance' => $this->fallbackCredit($user),
                        'free_num' => 0,
                        'multipy' => 0,
                        'drop_feature' => 0,
                        'drop_normal' => 0,
                    ];
                }
            } catch (\Throwable $e) {
                Log::warning('histories fallback failed', ['message' => $e->getMessage()]);
            }
        }

        return $this->fallbackSuccess([
            'totalRecord' => count($items),
            'perPage' => count($items) ?: 20,
            'currentPage' => 1,
            'displayTotal' => count($items),
            'totalPage' => 1,
            'totalBet' => array_sum(array_column($items, 'total_bet')),
            'totalProfit' => array_sum(array_column($items, 'profit')),
            'items' => $items,
        ], 'History list loaded');
    }

    public function historyDetail($id): JsonResponse
    {
        return $this->fallbackSuccess([
            'spin_date' => now()->format('Y-m-d'),
            'spin_hour' => now()->format('H:i:s'),
            'special_symbols' => [],
            'result_data' => [[
                'spin_title' => 'History #'.$id,
                'reel_type' => 'main',
                'win_amount' => 0,
                'icon_data' => [],
                'active_lines' => [],
                'drop_line' => [],
                'drop_data' => [],
                'total_bet' => 0,
                'profit' => 0,
                'free_num' => 0,
                'multipy' => 0,
            ]],
        ], 'History detail loaded');
    }

    public function collect(string $token): JsonResponse
    {
        return $this->fallbackSuccess([
            'credit' => $this->fallbackCredit($this->resolveUserFromGameToken($token)),
        ], 'Collect finished');
    }

    public function gamble(Request $request, string $token): JsonResponse
    {
        return $this->fallbackSuccess([
            'win_amount' => 0,
            'is_win' => false,
            'is_finish' => true,
            'credit' => $this->fallbackCredit($this->resolveUserFromGameToken($token)),
            'card' => $request->card,
        ], 'Gamble finished');
    }

    public function linenum(Request $request, string $token): JsonResponse
    {
        return $this->fallbackSuccess([
            'line_num' => (int) ($request->line ?? $request->line_num ?? 5),
        ], 'Line number changed');
    }

    public function pricing(): JsonResponse
    {
        return $this->fallbackSuccess([
            'jackpot_sum' => 0,
            'credit_line' => 1,
            'title' => 'Original Game',
        ], 'Pricing loaded');
    }

    public function checkFree(): JsonResponse
    {
        return $this->fallbackSuccess(['free' => false], 'Check free');
    }

    public function freeCredit(): JsonResponse
    {
        return $this->fallbackSuccess(['credit' => 0], 'Free credit');
    }

    public function checkLucky(): JsonResponse
    {
        return $this->fallbackSuccess(['eligible' => false], 'Check lucky');
    }

    public function luckyWheel(): JsonResponse
    {
        return $this->fallbackSuccess(['win_amount' => 0], 'Lucky wheel');
    }
}
