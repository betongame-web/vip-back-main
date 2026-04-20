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
use Illuminate\Support\Facades\Schema;
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
            ['id' => 101, 'name' => 'Fortune Tiger', 'slug' => 'fortune-tiger', 'game_code' => 'fortunetiger'],
            ['id' => 102, 'name' => 'Fortune Rabbit', 'slug' => 'fortune-rabbit', 'game_code' => 'fortunerabbit'],
            ['id' => 103, 'name' => 'Fortune Ox', 'slug' => 'fortune-ox', 'game_code' => 'fortuneox'],
            ['id' => 104, 'name' => 'Fortune Panda', 'slug' => 'fortune-panda', 'game_code' => 'fortunepanda'],
            ['id' => 105, 'name' => 'Fortune Mouse', 'slug' => 'fortune-mouse', 'game_code' => 'fortunemouse'],
            ['id' => 106, 'name' => 'Treasures of Aztec', 'slug' => 'treasures-of-aztec', 'game_code' => 'treasuresofaztec'],
            ['id' => 201, 'name' => 'Phoenix Rises', 'slug' => 'phoenix-rises', 'game_code' => 'phoenixrises'],
            ['id' => 202, 'name' => 'Queen of Bounty', 'slug' => 'queen-of-bounty', 'game_code' => 'queenofbounty'],
            ['id' => 203, 'name' => 'Jack Frost', 'slug' => 'jack-frost', 'game_code' => 'jackfrost'],
            ['id' => 204, 'name' => 'Songkran Party', 'slug' => 'songkran-party', 'game_code' => 'songkranparty'],
            ['id' => 205, 'name' => 'Bikini Paradise', 'slug' => 'bikini-paradise', 'game_code' => 'bikiniparadise'],
            ['id' => 206, 'name' => 'Hood vs Woolf', 'slug' => 'hood-vs-woolf', 'game_code' => 'hoodvswoolf'],
        ];
    }

    protected function hasTableColumn(string $table, string $column): bool
    {
        try {
            return Schema::hasColumn($table, $column);
        } catch (Throwable $e) {
            return false;
        }
    }

    protected function gameName($game): string
    {
        return (string) (
            $game->game_name
            ?? $game->name
            ?? $game->title
            ?? 'Unknown Game'
        );
    }

    protected function gameSlug($game): string
    {
        $raw = $game->slug ?? $game->game_code ?? Str::slug($this->gameName($game));
        return (string) $raw;
    }

    protected function gameCover($game): string
    {
        $cover = $game->cover ?? $game->image ?? $game->img ?? $game->thumbnail ?? null;

        if (!$cover) {
            return $this->fallbackCover();
        }

        $cover = (string) $cover;

        if (Str::startsWith($cover, ['http://', 'https://'])) {
            return $cover;
        }

        return secure_url('/' . ltrim($cover, '/'));
    }

    protected function providerName($provider): string
    {
        return (string) ($provider->name ?? $provider->title ?? 'Original Game');
    }

    protected function providerSlug($provider): string
    {
        return (string) (
            $provider->slug
            ?? $provider->code
            ?? Str::slug($this->providerName($provider))
        );
    }

    protected function normalizeProvider($provider): array
    {
        return [
            'id' => $provider->id ?? null,
            'name' => $this->providerName($provider),
            'slug' => $this->providerSlug($provider),
        ];
    }

    protected function normalizeGame($game): array
    {
        $categories = [];

        if (method_exists($game, 'relationLoaded') && $game->relationLoaded('categories') && $game->categories) {
            $categories = $game->categories->map(function ($category) {
                return [
                    'id' => $category->id ?? null,
                    'name' => $category->name ?? $category->title ?? 'Category',
                    'slug' => $category->slug ?? Str::slug($category->name ?? $category->title ?? 'category'),
                ];
            })->values()->toArray();
        }

        $provider = null;
        if (method_exists($game, 'relationLoaded') && $game->relationLoaded('provider') && $game->provider) {
            $provider = $this->normalizeProvider($game->provider);
        } else {
            $provider = [
                'id' => $game->provider_id ?? null,
                'name' => 'Original Game',
                'slug' => 'original-game',
            ];
        }

        return [
            'id' => $game->id ?? null,
            'game_name' => $this->gameName($game),
            'name' => $this->gameName($game),
            'slug' => $this->gameSlug($game),
            'game_code' => (string) ($game->game_code ?? ''),
            'cover' => $this->gameCover($game),
            'distribution' => (string) ($game->distribution ?? 'source'),
            'status' => (bool) ($game->status ?? true),
            'views' => (int) ($game->views ?? 0),
            'provider' => $provider,
            'categories' => $categories,
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
                'game_name' => $game['name'],
                'name' => $game['name'],
                'slug' => $game['slug'],
                'game_code' => $game['game_code'],
                'cover' => $this->fallbackCover(),
                'distribution' => 'source',
                'status' => true,
                'views' => 0,
                'provider' => [
                    'id' => $index < 6 ? 1 : 2,
                    'name' => $index < 6 ? 'Original Slots A' : 'Original Slots B',
                    'slug' => $index < 6 ? 'original-slots-a' : 'original-slots-b',
                ],
                'categories' => [
                    ['id' => 1, 'name' => 'Slots', 'slug' => 'slots'],
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
                'game_name' => $games[0]['name'],
                'name' => $games[0]['name'],
                'slug' => $games[0]['slug'],
                'game_code' => $games[0]['game_code'],
                'cover' => $this->fallbackCover(),
                'distribution' => 'source',
                'status' => true,
                'views' => 0,
                'provider' => [
                    'id' => 1,
                    'name' => 'Original Slots A',
                    'slug' => 'original-slots-a',
                ],
                'categories' => [
                    ['id' => 1, 'name' => 'Slots', 'slug' => 'slots'],
                ],
            ],
            [
                'id' => $games[1]['id'],
                'game_name' => $games[1]['name'],
                'name' => $games[1]['name'],
                'slug' => $games[1]['slug'],
                'game_code' => $games[1]['game_code'],
                'cover' => $this->fallbackCover(),
                'distribution' => 'source',
                'status' => true,
                'views' => 0,
                'provider' => [
                    'id' => 1,
                    'name' => 'Original Slots A',
                    'slug' => 'original-slots-a',
                ],
                'categories' => [
                    ['id' => 1, 'name' => 'Slots', 'slug' => 'slots'],
                ],
            ],
            [
                'id' => $games[6]['id'],
                'game_name' => $games[6]['name'],
                'name' => $games[6]['name'],
                'slug' => $games[6]['slug'],
                'game_code' => $games[6]['game_code'],
                'cover' => $this->fallbackCover(),
                'distribution' => 'source',
                'status' => true,
                'views' => 0,
                'provider' => [
                    'id' => 2,
                    'name' => 'Original Slots B',
                    'slug' => 'original-slots-b',
                ],
                'categories' => [
                    ['id' => 1, 'name' => 'Slots', 'slug' => 'slots'],
                ],
            ],
        ];
    }

    protected function fallbackSingleGame(string $id): array
    {
        $map = collect($this->originalGameMap())
            ->keyBy(fn ($item) => (string) $item['id'])
            ->toArray();

        $base = $map[$id] ?? $map['101'];

        $providerId = ((int) $base['id'] < 200) ? 1 : 2;
        $providerName = ((int) $base['id'] < 200) ? 'Original Slots A' : 'Original Slots B';
        $providerSlug = ((int) $base['id'] < 200) ? 'original-slots-a' : 'original-slots-b';

        return [
            'id' => $base['id'],
            'game_name' => $base['name'],
            'name' => $base['name'],
            'slug' => $base['slug'],
            'game_code' => $base['game_code'],
            'cover' => $this->fallbackCover(),
            'distribution' => 'source',
            'status' => true,
            'views' => 0,
            'provider' => [
                'id' => $providerId,
                'name' => $providerName,
                'slug' => $providerSlug,
            ],
            'categories' => [
                ['id' => 1, 'name' => 'Slots', 'slug' => 'slots'],
            ],
        ];
    }

    protected function fallbackGamesPaginated(Request $request): array
    {
        $games = array_map(function ($game) {
            $providerId = ((int) $game['id'] < 200) ? 1 : 2;
            $providerName = ((int) $game['id'] < 200) ? 'Original Slots A' : 'Original Slots B';
            $providerSlug = ((int) $game['id'] < 200) ? 'original-slots-a' : 'original-slots-b';

            return [
                'id' => $game['id'],
                'game_name' => $game['name'],
                'name' => $game['name'],
                'slug' => $game['slug'],
                'game_code' => $game['game_code'],
                'cover' => $this->fallbackCover(),
                'distribution' => 'source',
                'status' => true,
                'views' => 0,
                'provider' => [
                    'id' => $providerId,
                    'name' => $providerName,
                    'slug' => $providerSlug,
                ],
                'categories' => [
                    ['id' => 1, 'name' => 'Slots', 'slug' => 'slots'],
                ],
            ];
        }, $this->originalGameMap());

        $searchTerm = trim((string) $request->get('searchTerm', ''));

        if ($searchTerm !== '') {
            $games = array_values(array_filter($games, function ($game) use ($searchTerm) {
                $needle = Str::lower($searchTerm);

                return str_contains(Str::lower($game['game_name']), $needle)
                    || str_contains(Str::lower($game['game_code']), $needle)
                    || str_contains(Str::lower($game['slug']), $needle);
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

    public function index()
    {
        try {
            $providers = Provider::with(['games', 'games.provider'])->get();

            $normalized = $providers->map(function ($provider) {
                $games = collect($provider->games ?? [])
                    ->filter(function ($game) {
                        if ($this->hasTableColumn('games', 'status')) {
                            return (bool) ($game->status ?? false);
                        }

                        return true;
                    })
                    ->map(function ($game) {
                        return $this->normalizeGame($game);
                    })
                    ->values()
                    ->toArray();

                return [
                    'id' => $provider->id ?? null,
                    'name' => $this->providerName($provider),
                    'slug' => $this->providerSlug($provider),
                    'games' => $games,
                ];
            })->filter(function ($provider) {
                return !empty($provider['games']);
            })->values();

            if ($normalized->isEmpty()) {
                return response()->json([
                    'providers' => $this->fallbackProviders(),
                    'fallback' => true,
                ], 200);
            }

            return response()->json([
                'providers' => $normalized,
            ], 200);
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
        try {
            $query = Game::query()->with(['provider', 'categories']);

            if ($this->hasTableColumn('games', 'status')) {
                $query->where('status', 1);
            }

            if ($this->hasTableColumn('games', 'is_featured')) {
                $query->where('is_featured', 1);
            }

            if ($this->hasTableColumn('games', 'views')) {
                $query->orderByDesc('views');
            } else {
                $query->orderByDesc('id');
            }

            $featuredGames = $query->limit(6)->get()
                ->map(function ($game) {
                    return $this->normalizeGame($game);
                })
                ->values();

            if ($featuredGames->isEmpty()) {
                return response()->json([
                    'featured_games' => $this->fallbackFeaturedGames(),
                    'games' => $this->fallbackFeaturedGames(),
                    'fallback' => true,
                ], 200);
            }

            return response()->json([
                'featured_games' => $featuredGames,
                'games' => $featuredGames,
            ], 200);
        } catch (Throwable $e) {
            Log::error('GameController@featured failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'featured_games' => $this->fallbackFeaturedGames(),
                'games' => $this->fallbackFeaturedGames(),
                'fallback' => true,
            ], 200);
        }
    }

    public function sourceProvider(Request $request, $token, $action)
    {
        try {
            $tokenOpen = \Helper::DecToken($token);
            $validEndpoints = ['session', 'icons', 'spin', 'freenum', 'buy', 'logs', 'save', 'histories', 'collect', 'gamble', 'linenum'];

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

            $game = Game::query()
                ->where('game_code', $tokenOpen['game'])
                ->first();

            if (!$game) {
                return response()->json([
                    'success' => false,
                    'message' => 'Game not found',
                    'data' => null,
                ], 404);
            }

            if ($this->hasTableColumn('games', 'status') && !(bool) ($game->status ?? false)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Game inactive',
                    'data' => null,
                ], 403);
            }

            $controller = \Helper::createController($game->game_code);

            if (!$controller) {
                return response()->json([
                    'success' => false,
                    'message' => 'Game controller not available',
                    'data' => null,
                ], 500);
            }

            return match ($action) {
                'session' => $controller->session($token),
                'spin' => $controller->spin($request, $token),
                'freenum' => $controller->freenum($request, $token),
                'icons' => response()->json([
                    'success' => true,
                    'message' => 'Icons success',
                    'data' => $controller->icons(),
                ]),
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
                    $checkExist->delete();

                    return response()->json([
                        'status' => true,
                        'message' => 'Removed successfully',
                    ], 200);
                }

                GameFavorite::create([
                    'user_id' => auth('api')->id(),
                    'game_id' => $id,
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Created successfully',
                ], 200);
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
                    $checkExist->delete();

                    return response()->json([
                        'status' => true,
                        'message' => 'Removed successfully',
                    ], 200);
                }

                GameLike::create([
                    'user_id' => auth('api')->id(),
                    'game_id' => $id,
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Created successfully',
                ], 200);
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

            $game = Game::with(['categories', 'provider'])->find($id);

            if (!$game) {
                return response()->json([
                    'game' => $this->fallbackSingleGame($id),
                    'gameUrl' => null,
                    'fallback' => true,
                ], 200);
            }

            if ($this->hasTableColumn('games', 'status') && !(bool) ($game->status ?? false)) {
                return response()->json([
                    'error' => 'Game inactive',
                ], 403);
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

            $totalBalance = (float) ($wallet->total_balance ?? 0);

            if ($totalBalance <= 0) {
                $totalBalance =
                    (float) ($wallet->balance ?? 0) +
                    (float) ($wallet->balance_bonus ?? $wallet->bonus_balance ?? 0) +
                    (float) ($wallet->balance_withdrawal ?? $wallet->withdrawable_balance ?? 0);
            }

            if ($totalBalance <= 0) {
                return response()->json([
                    'action' => 'deposit',
                    'error' => 'Insufficient balance',
                ], 402);
            }

            if ($this->hasTableColumn('games', 'views')) {
                $game->increment('views');
            }

            $token = \Helper::MakeToken([
                'id' => auth('api')->id(),
                'game' => $game->game_code,
            ]);

            if (($game->distribution ?? 'source') !== 'source') {
                return response()->json([
                    'error' => 'Unsupported game distribution',
                ], 422);
            }

            $baseUrl = rtrim($request->getSchemeAndHttpHost(), '/');

            return response()->json([
                'game' => $this->normalizeGame($game),
                'gameUrl' => $baseUrl . '/originals/' . $game->game_code . '/index.html?token=' . urlencode($token),
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

            if ($this->hasTableColumn('games', 'status')) {
                $query->where('status', 1);
            }

            if (!empty($request->provider) && $request->provider !== 'all' && $this->hasTableColumn('games', 'provider_id')) {
                $query->where('provider_id', $request->provider);
            }

            if (!empty($request->category) && $request->category !== 'all') {
                $query->whereHas('categories', function ($categoryQuery) use ($request) {
                    $categoryQuery->where('slug', $request->category);
                });
            }

            $search = trim((string) $request->searchTerm);

            if ($search !== '' && strlen($search) > 1) {
                $searchableColumns = array_values(array_filter([
                    $this->hasTableColumn('games', 'game_code') ? 'game_code' : null,
                    $this->hasTableColumn('games', 'game_name') ? 'game_name' : null,
                    $this->hasTableColumn('games', 'name') ? 'name' : null,
                    $this->hasTableColumn('games', 'slug') ? 'slug' : null,
                    $this->hasTableColumn('games', 'description') ? 'description' : null,
                    $this->hasTableColumn('games', 'distribution') ? 'distribution' : null,
                ]));

                if (!empty($searchableColumns)) {
                    $query->where(function ($q) use ($searchableColumns, $search) {
                        foreach ($searchableColumns as $index => $column) {
                            if ($index === 0) {
                                $q->where($column, 'like', '%' . $search . '%');
                            } else {
                                $q->orWhere($column, 'like', '%' . $search . '%');
                            }
                        }
                    });
                }
            } else {
                if ($this->hasTableColumn('games', 'views')) {
                    $query->orderByDesc('views');
                } else {
                    $query->orderByDesc('id');
                }
            }

            $games = $query->paginate(12)->appends(request()->query());

            $games->setCollection(
                $games->getCollection()->map(function ($game) {
                    return $this->normalizeGame($game);
                })
            );

            if ($games->isEmpty()) {
                return response()->json([
                    'games' => $this->fallbackGamesPaginated($request),
                    'fallback' => true,
                ], 200);
            }

            return response()->json([
                'games' => $games,
            ], 200);
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