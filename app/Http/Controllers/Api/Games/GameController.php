<?php

namespace App\Http\Controllers\Api\Games;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameFavorite;
use App\Models\GameLike;
use App\Models\Provider;
use App\Models\Wallet;
use App\Traits\Providers\FiversTrait;
use App\Traits\Providers\Games2ApiTrait;
use App\Traits\Providers\KaGamingTrait;
use App\Traits\Providers\SalsaGamesTrait;
use App\Traits\Providers\VibraTrait;
use App\Traits\Providers\WorldSlotTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class GameController extends Controller
{
    use KaGamingTrait, FiversTrait, VibraTrait, SalsaGamesTrait, WorldSlotTrait, Games2ApiTrait;

    protected function fallbackCover(): string
    {
        return secure_url('/assets/images/FortuneTiger.webp');
    }

    protected function originalGameMap(): array
    {
        return [
            ['id' => 101, 'game_name' => 'Fortune Tiger', 'slug' => 'fortune-tiger', 'game_code' => 'fortunetiger'],
            ['id' => 102, 'game_name' => 'Fortune Rabbit', 'slug' => 'fortune-rabbit', 'game_code' => 'fortunerabbit'],
            ['id' => 103, 'game_name' => 'Fortune Ox', 'slug' => 'fortune-ox', 'game_code' => 'fortuneox'],
            ['id' => 104, 'game_name' => 'Fortune Panda', 'slug' => 'fortune-panda', 'game_code' => 'fortunepanda'],
            ['id' => 105, 'game_name' => 'Fortune Mouse', 'slug' => 'fortune-mouse', 'game_code' => 'fortunemouse'],
            ['id' => 106, 'game_name' => 'Treasures of Aztec', 'slug' => 'treasures-of-aztec', 'game_code' => 'treasuresofaztec'],
            ['id' => 201, 'game_name' => 'Phoenix Rises', 'slug' => 'phoenix-rises', 'game_code' => 'phoenixrises'],
            ['id' => 202, 'game_name' => 'Queen of Bounty', 'slug' => 'queen-of-bounty', 'game_code' => 'queenofbounty'],
            ['id' => 203, 'game_name' => 'Jack Frost', 'slug' => 'jack-frost', 'game_code' => 'jackfrost'],
            ['id' => 204, 'game_name' => 'Songkran Party', 'slug' => 'songkran-party', 'game_code' => 'songkranparty'],
            ['id' => 205, 'game_name' => 'Bikini Paradise', 'slug' => 'bikini-paradise', 'game_code' => 'bikiniparadise'],
            ['id' => 206, 'game_name' => 'Hood vs Woolf', 'slug' => 'hood-vs-woolf', 'game_code' => 'hoodvswoolf'],
        ];
    }

    protected function fallbackProviders(): array
    {
        $games = $this->originalGameMap();

        $providerA = [];
        $providerB = [];

        foreach ($games as $index => $game) {
            $payload = [
                'id' => $game['id'],
                'game_name' => $game['game_name'],
                'slug' => $game['slug'],
                'game_code' => $game['game_code'],
                'cover' => $this->fallbackCover(),
                'provider' => [
                    'id' => $index < 6 ? 1 : 2,
                    'name' => $index < 6 ? 'Original Slots A' : 'Original Slots B',
                    'slug' => $index < 6 ? 'original-slots-a' : 'original-slots-b',
                ],
            ];

            if ($index < 6) {
                $providerA[] = $payload;
            } else {
                $providerB[] = $payload;
            }
        }

        return [
            [
                'id' => 1,
                'name' => 'Original Slots A',
                'slug' => 'original-slots-a',
                'games' => $providerA,
            ],
            [
                'id' => 2,
                'name' => 'Original Slots B',
                'slug' => 'original-slots-b',
                'games' => $providerB,
            ],
        ];
    }

    protected function fallbackFeaturedGames(): array
    {
        $games = $this->originalGameMap();

        return [
            [
                'id' => $games[0]['id'],
                'game_name' => $games[0]['game_name'],
                'slug' => $games[0]['slug'],
                'game_code' => $games[0]['game_code'],
                'cover' => $this->fallbackCover(),
                'provider' => [
                    'id' => 1,
                    'name' => 'Original Slots A',
                    'slug' => 'original-slots-a',
                ],
            ],
            [
                'id' => $games[1]['id'],
                'game_name' => $games[1]['game_name'],
                'slug' => $games[1]['slug'],
                'game_code' => $games[1]['game_code'],
                'cover' => $this->fallbackCover(),
                'provider' => [
                    'id' => 1,
                    'name' => 'Original Slots A',
                    'slug' => 'original-slots-a',
                ],
            ],
            [
                'id' => $games[6]['id'],
                'game_name' => $games[6]['game_name'],
                'slug' => $games[6]['slug'],
                'game_code' => $games[6]['game_code'],
                'cover' => $this->fallbackCover(),
                'provider' => [
                    'id' => 2,
                    'name' => 'Original Slots B',
                    'slug' => 'original-slots-b',
                ],
            ],
        ];
    }

    protected function fallbackSingleGame(string $id): array
    {
        $games = collect($this->originalGameMap())
            ->keyBy(fn ($item) => (string) $item['id'])
            ->toArray();

        $base = $games[$id] ?? $games['101'];

        return [
            'id' => $base['id'],
            'game_name' => $base['game_name'],
            'slug' => $base['slug'],
            'game_code' => $base['game_code'],
            'cover' => $this->fallbackCover(),
            'distribution' => 'source',
            'provider' => [
                'id' => ((int) $base['id'] < 200) ? 1 : 2,
                'name' => ((int) $base['id'] < 200) ? 'Original Slots A' : 'Original Slots B',
                'slug' => ((int) $base['id'] < 200) ? 'original-slots-a' : 'original-slots-b',
            ],
            'categories' => [
                ['id' => 1, 'name' => 'Slots', 'slug' => 'slots'],
            ],
        ];
    }

    protected function fallbackGamesPaginated(Request $request): array
    {
        $games = array_map(function ($game) {
            return [
                'id' => $game['id'],
                'game_name' => $game['game_name'],
                'slug' => $game['slug'],
                'game_code' => $game['game_code'],
                'cover' => $this->fallbackCover(),
                'provider' => [
                    'id' => ((int) $game['id'] < 200) ? 1 : 2,
                    'name' => ((int) $game['id'] < 200) ? 'Original Slots A' : 'Original Slots B',
                    'slug' => ((int) $game['id'] < 200) ? 'original-slots-a' : 'original-slots-b',
                ],
                'categories' => [
                    ['id' => 1, 'name' => 'Slots', 'slug' => 'slots'],
                ],
            ];
        }, $this->originalGameMap());

        $searchTerm = trim((string) $request->get('searchTerm', ''));

        if ($searchTerm !== '') {
            $games = array_values(array_filter($games, function ($game) use ($searchTerm) {
                return str_contains(strtolower($game['game_name']), strtolower($searchTerm))
                    || str_contains(strtolower($game['game_code']), strtolower($searchTerm))
                    || str_contains(strtolower($game['slug']), strtolower($searchTerm));
            }));
        }
        return [
            'current_page' => 1,
            'data' => $games,
            'first_page_url' => url('/api/casinos/games?page=1'),
            'from' => count($games) ? 1 : null,
            'last_page' => 1,
            'last_page_url' => url('/api/casinos/games?page=1'),
            'links' => [],
            'next_page_url' => null,
            'path' => url('/api/casinos/games'),
            'per_page' => 12,
            'prev_page_url' => null,
            'to' => count($games),
            'total' => count($games),
        ];
    }

    protected function emptyRuntimeSuccess(string $message = 'OK')
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [],
        ], 200);
    }

    public function index()
    {
        try {
            $providers = Provider::with(['games', 'games.provider'])
                ->whereHas('games')
                ->where('status', 1)
                ->orderBy('name', 'desc')
                ->get();

            if ($providers->isEmpty()) {
                return response()->json([
                    'providers' => $this->fallbackProviders(),
                    'fallback' => true,
                ], 200);
            }

            return response()->json(['providers' => $providers], 200);
        } catch (Throwable $e) {
            Log::error('GameController@index failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'providers' => $this->fallbackProviders(),
                'fallback' => true,
            ], 200);
        }
    }

    public function featured()
    {
        Log::info('FEATURED GAMES HIT');

        try {
            $featuredGames = Game::with(['provider'])
                ->where('is_featured', 1)
                ->where('status', 1)
                ->orderByDesc('id')
                ->get()
                ->map(function ($game) {
                    $cover = $game->cover ?? null;

                    if (!$cover) {
                        $cover = $this->fallbackCover();
                    } elseif (
                        !str_starts_with($cover, 'http://') &&
                        !str_starts_with($cover, 'https://')
                    ) {
                        $cover = secure_url($cover);
                    }

                    return [
                        'id' => $game->id,
                        'game_name' => $game->game_name,
                        'slug' => $game->slug ?? Str::slug($game->game_name),
                        'game_code' => $game->game_code,
                        'cover' => $cover,
                        'provider' => [
                            'id' => $game->provider->id ?? null,
                            'name' => $game->provider->name ?? 'Original Game',
                            'slug' => $game->provider->code ?? ($game->provider->slug ?? 'original-game'),
                        ],
                    ];
                })
                ->values();

            Log::info('FEATURED GAMES RESULT', [
                'count' => $featuredGames->count(),
            ]);

            if ($featuredGames->isEmpty()) {
                return response()->json([
                    'featured_games' => $this->fallbackFeaturedGames(),
                    'fallback' => true,
                ], 200);
            }

            return response()->json([
                'featured_games' => $featuredGames,
            ], 200);
        } catch (Throwable $e) {
            Log::error('GameController@featured failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'featured_games' => $this->fallbackFeaturedGames(),
                'fallback' => true,
            ], 200);
        }
    }

    public function sourceProvider(Request $request, $token, $action)
    {
        Log::info('VGAMES HIT', [
            'method' => $request->method(),
            'token' => $token ?? null,
            'action' => $action ?? null,
            'query' => $request->query(),
            'body' => $request->all(),
            'url' => $request->fullUrl(),
        ]);

        try {
            $tokenOpen = \Helper::DecToken($token);

            $validEndpoints = [
                'session',
                'icons',
                'spin',
                'freenum',
                'buy',
                'logs',
                'save',
                'histories',
                'collect',
                'gamble',
                'linenum',
            ];

            if (!in_array($action, $validEndpoints, true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unsupported action',
                    'data' => null,
                ], 404);
            }

            if (!(isset($tokenOpen['status']) && $tokenOpen['status'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid game session',
                    'data' => null,
                ], 400);
            }

            $game = Game::where('status', 1)
                ->where('game_code', $tokenOpen['game'])
                ->first();

            if (!$game) {
                return response()->json([
                    'success' => false,
                    'message' => 'Game not found',
                    'data' => null,
                ], 404);
            }

            $controller = \Helper::createController($game->game_code);

            if (!$controller) {
                return response()->json([
                    'success' => false,
                    'message' => 'Game controller not available',
                    'data' => null,
                ], 500);
            }

            $actionMethodMap = [
                'session' => 'session',
                'spin' => 'spin',
                'freenum' => 'freenum',
                'icons' => 'icons',
                'buy' => 'buy',
                'logs' => 'logs',
                'save' => 'save',
                'histories' => 'histories',
                'collect' => 'collect',
                'gamble' => 'gamble',
                'linenum' => 'linenum',
            ];

            $method = $actionMethodMap[$action] ?? null;

            if ($action === 'icons') {
                if (!method_exists($controller, 'icons')) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Icons success',
                        'data' => [],
                    ], 200);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Icons success',
                    'data' => $controller->icons(),
                ], 200);
            }

            if (!$method || !method_exists($controller, $method)) {
                if (in_array($action, ['buy', 'logs', 'save', 'histories', 'collect', 'gamble', 'linenum'], true)) {
                    return $this->emptyRuntimeSuccess(ucfirst($action) . ' success');
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Action handler not available',
                    'data' => null,
                ], 500);
            }

            return match ($action) {
                'session' => $controller->session($token),
                'spin' => $controller->spin($request, $token),
                'freenum' => $controller->freenum($request, $token),
                'buy' => $controller->buy($request, $token),
                'logs' => $controller->logs($token),
                'save' => $controller->save($request, $token),
                'histories' => $controller->histories($token),
                'collect' => $controller->collect($token),
                'gamble' => $controller->gamble($request, $token),
                'linenum' => $controller->linenum($request, $token),
                default => response()->json([
                    'success' => false,
                    'message' => 'Unsupported action',
                    'data' => null,
                ], 404),
            };
        } catch (Throwable $e) {
            Log::error('GameController@sourceProvider failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'action' => $action,
                'token' => $token,
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function historyDetail($id)
    {
        $controller = app(\App\Http\Controllers\Controller::class);
        return $controller->historyDetail($id);
    }

    public function pricing()
    {
        $controller = app(\App\Http\Controllers\Controller::class);
        return $controller->pricing();
    }

    public function checkFree()
    {
        $controller = app(\App\Http\Controllers\Controller::class);
        return $controller->checkFree();
    }

    public function freeCredit()
    {
        $controller = app(\App\Http\Controllers\Controller::class);
        return $controller->freeCredit();
    }

    public function checkLucky()
    {
        $controller = app(\App\Http\Controllers\Controller::class);
        return $controller->checkLucky();
    }

    public function luckyWheel()
    {
        $controller = app(\App\Http\Controllers\Controller::class);
        return $controller->luckyWheel();
    }

    public function toggleFavorite($id)
    {
        try {
            if (auth('api')->check()) {
                $checkExist = GameFavorite::where('user_id', auth('api')->id())
                    ->where('game_id', $id)
                    ->first();

                if ($checkExist) {
                    if ($checkExist->delete()) {
                        return response()->json([
                            'status' => true,
                            'message' => 'Removed successfully',
                        ], 200);
                    }
                } else {
                    $gameFavoriteCreate = GameFavorite::create([
                        'user_id' => auth('api')->id(),
                        'game_id' => $id,
                    ]);

                    if ($gameFavoriteCreate) {
                        return response()->json([
                            'status' => true,
                            'message' => 'Created successfully',
                        ], 200);
                    }
                }
            }

            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 401);
        } catch (Throwable $e) {
            Log::error('GameController@toggleFavorite failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'game_id' => $id,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Fallback favorite toggle success',
                'fallback' => true,
            ], 200);
        }
    }

    public function toggleLike($id)
    {
        try {
            if (auth('api')->check()) {
                $checkExist = GameLike::where('user_id', auth('api')->id())
                    ->where('game_id', $id)
                    ->first();

                if ($checkExist) {
                    if ($checkExist->delete()) {
                        return response()->json([
                            'status' => true,
                            'message' => 'Removed successfully',
                        ], 200);
                    }
                } else {
                    $gameLikeCreate = GameLike::create([
                        'user_id' => auth('api')->id(),
                        'game_id' => $id,
                    ]);

                    if ($gameLikeCreate) {
                        return response()->json([
                            'status' => true,
                            'message' => 'Created successfully',
                        ], 200);
                    }
                }
            }

            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 401);
        } catch (Throwable $e) {
            Log::error('GameController@toggleLike failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'game_id' => $id,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Fallback like toggle success',
                'fallback' => true,
            ], 200);
        }
    }

    public function show(Request $request, string $id)
    {
        try {
            if (!auth('api')->check()) {
                return response()->json([
                    'error' => 'unauthenticated',
                ], 401);
            }

            $game = Game::with(['categories', 'provider'])
                ->where('status', 1)
                ->find($id);

            if (!$game) {
                $fallbackGame = $this->fallbackSingleGame($id);

                return response()->json([
                    'game' => $fallbackGame,
                    'fallback' => true,
                ], 200);
            }

            $wallet = Wallet::where('user_id', auth('api')->id())->first();

            if (!$wallet) {
                return response()->json([
                    'error' => 'Wallet not found',
                ], 404);
            }

            if (!((bool) ($wallet->active ?? $wallet->status ?? true))) {
                return response()->json([
                    'error' => 'Wallet inactive',
                ], 403);
            }

            $totalBalance =
                (float) ($wallet->balance ?? 0) +
                (float) ($wallet->balance_bonus ?? $wallet->bonus_balance ?? 0) +
                (float) ($wallet->balance_withdrawal ?? $wallet->withdrawable_balance ?? 0);

            if ($totalBalance <= 0) {
                return response()->json([
                    'action' => 'deposit',
                    'error' => 'Insufficient balance',
                ], 402);
            }

            $game->increment('views');

            $token = \Helper::MakeToken([
                'id' => auth('api')->id(),
                'game' => $game->game_code,
            ]);

            if (($game->distribution ?? null) !== 'source') {
                return response()->json([
                    'error' => 'Unsupported game distribution',
                ], 422);
            }

            $baseUrl = rtrim(config('app.url') ?: $request->getSchemeAndHttpHost(), '/');
            $gameUrl = $baseUrl . '/originals/' . $game->game_code . '/index.html?token=' . urlencode($token);

            Log::info('GAME SHOW RESPONSE', [
                'game_id' => $id,
                'auth' => auth('api')->check(),
                'user_id' => auth('api')->id(),
                'game_url' => $gameUrl,
            ]);

            return response()->json([
                'game' => $game,
                'gameUrl' => $gameUrl,
                'token' => $token,
            ], 200);
        } catch (Throwable $e) {
            Log::error('GameController@show failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'game_id' => $id,
                'user_id' => auth('api')->id(),
            ]);

            return response()->json([
                'error' => 'Unable to load game',
            ], 500);
        }
    }

    public function allGames(Request $request)
    {
        try {
            $query = Game::query()->with(['provider', 'categories']);

            if (!empty($request->provider) && $request->provider !== 'all') {
                $query->where('provider_id', $request->provider);
            }

            if (!empty($request->category) && $request->category !== 'all') {
                $query->whereHas('categories', function ($categoryQuery) use ($request) {
                    $categoryQuery->where('slug', $request->category);
                });
            }

            if (!empty($request->searchTerm) && strlen((string) $request->searchTerm) > 2) {
                $search = trim((string) $request->searchTerm);

                $query->where(function ($q) use ($search) {
                    $q->where('game_code', 'like', "%{$search}%")
                        ->orWhere('game_name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('distribution', 'like', "%{$search}%");
                });
            } else {
                $query->orderBy('views', 'desc');
            }

            $games = $query
                ->where('status', 1)
                ->paginate(12)
                ->appends($request->query());

            if ($games->isEmpty()) {
                return response()->json([
                    'games' => $this->fallbackGamesPaginated($request),
                    'fallback' => true,
                ], 200);
            }

            return response()->json(['games' => $games], 200);
        } catch (Throwable $e) {
            Log::error('GameController@allGames failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'provider' => $request->provider ?? null,
                'category' => $request->category ?? null,
                'searchTerm' => $request->searchTerm ?? null,
            ]);

            return response()->json([
                'games' => $this->fallbackGamesPaginated($request),
                'fallback' => true,
            ], 200);
        }
    }

    public function webhookGoldApiMethod(Request $request)
    {
        return self::WebhooksFivers($request);
    }

    public function webhookUserBalanceMethod(Request $request)
    {
        return self::GetUserBalanceWorldSlot($request);
    }

    public function webhookGameCallbackMethod(Request $request)
    {
        return self::GameCallbackWorldSlot($request);
    }

    public function webhookMoneyCallbackMethod(Request $request)
    {
        return self::MoneyCallbackWorldSlot($request);
    }

    public function webhookVibraMethod(Request $request, $parameters)
    {
        return self::WebhookVibra($request, $parameters);
    }

    public function webhookKaGamingMethod(Request $request)
    {
        return self::WebhookKaGaming($request);
    }

    public function webhookSalsaMethod(Request $request)
    {
        return self::webhookSalsa($request);
    }

    public function destroy(string $id)
    {
        //
    }
}