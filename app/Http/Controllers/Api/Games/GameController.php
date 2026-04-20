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

    protected function hasTableColumn(string $table, string $column): bool
    {
        try {
            return Schema::hasColumn($table, $column);
        } catch (Throwable $e) {
            return false;
        }
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

    protected function gameName($game): string
    {
        return (string) ($game->game_name ?? $game->name ?? 'Unknown Game');
    }

    protected function gameSlug($game): string
    {
        return (string) ($game->slug ?? $game->game_code ?? Str::slug($this->gameName($game)));
    }

    protected function gameCover($game): string
    {
        $cover = $game->cover ?? $game->image ?? null;

        if (!$cover) {
            return $this->fallbackCover();
        }

        $cover = (string) $cover;

        if (Str::startsWith($cover, ['http://', 'https://'])) {
            return $cover;
        }

        return secure_url('/' . ltrim($cover, '/'));
    }

    protected function normalizeGame($game): array
    {
        $provider = [
            'id' => $game->provider->id ?? $game->provider_id ?? null,
            'name' => $game->provider->name ?? 'Original Game',
            'slug' => $game->provider->slug ?? $game->provider->code ?? 'original-game',
        ];

        $categories = [];
        if (isset($game->categories) && $game->categories) {
            $categories = collect($game->categories)->map(function ($category) {
                return [
                    'id' => $category->id ?? null,
                    'name' => $category->name ?? 'Category',
                    'slug' => $category->slug ?? Str::slug($category->name ?? 'category'),
                ];
            })->values()->toArray();
        }

        return [
            'id' => $game->id ?? null,
            'name' => $this->gameName($game),
            'game_name' => $this->gameName($game),
            'slug' => $this->gameSlug($game),
            'game_code' => (string) ($game->game_code ?? ''),
            'cover' => $this->gameCover($game),
            'image' => $this->gameCover($game),
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
                'name' => $game['name'],
                'game_name' => $game['name'],
                'slug' => $game['slug'],
                'game_code' => $game['game_code'],
                'cover' => $this->fallbackCover(),
                'image' => $this->fallbackCover(),
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
                'name' => $games[0]['name'],
                'game_name' => $games[0]['name'],
                'slug' => $games[0]['slug'],
                'game_code' => $games[0]['game_code'],
                'cover' => $this->fallbackCover(),
                'image' => $this->fallbackCover(),
                'provider' => [
                    'id' => 1,
                    'name' => 'Original Slots A',
                    'slug' => 'original-slots-a',
                ],
            ],
            [
                'id' => $games[1]['id'],
                'name' => $games[1]['name'],
                'game_name' => $games[1]['name'],
                'slug' => $games[1]['slug'],
                'game_code' => $games[1]['game_code'],
                'cover' => $this->fallbackCover(),
                'image' => $this->fallbackCover(),
                'provider' => [
                    'id' => 1,
                    'name' => 'Original Slots A',
                    'slug' => 'original-slots-a',
                ],
            ],
            [
                'id' => $games[6]['id'],
                'name' => $games[6]['name'],
                'game_name' => $games[6]['name'],
                'slug' => $games[6]['slug'],
                'game_code' => $games[6]['game_code'],
                'cover' => $this->fallbackCover(),
                'image' => $this->fallbackCover(),
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
            'name' => $base['name'],
            'game_name' => $base['name'],
            'slug' => $base['slug'],
            'game_code' => $base['game_code'],
            'cover' => $this->fallbackCover(),
            'image' => $this->fallbackCover(),
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
                'name' => $game['name'],
                'game_name' => $game['name'],
                'slug' => $game['slug'],
                'game_code' => $game['game_code'],
                'cover' => $this->fallbackCover(),
                'image' => $this->fallbackCover(),
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
                $needle = strtolower($searchTerm);

                return str_contains(strtolower($game['game_name']), $needle)
                    || str_contains(strtolower($game['game_code']), $needle)
                    || str_contains(strtolower($game['slug']), $needle);
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
            $query = Provider::with(['games', 'games.provider']);

            if ($this->hasTableColumn('providers', 'status')) {
                $query->where('status', 1);
            }

            $providers = $query->get();

            $normalized = $providers->map(function ($provider) {
                $games = collect($provider->games ?? [])->filter(function ($game) {
                    if ($this->hasTableColumn('games', 'status')) {
                        return (bool) ($game->status ?? false);
                    }

                    return true;
                })->map(function ($game) {
                    return $this->normalizeGame($game);
                })->values()->toArray();

                return [
                    'id' => $provider->id ?? null,
                    'name' => $provider->name ?? 'Provider',
                    'slug' => $provider->slug ?? $provider->code ?? Str::slug($provider->name ?? 'provider'),
                    'games' => $games,
                ];
            })->filter(fn ($provider) => !empty($provider['games']))->values();

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
        Log::info('FEATURED GAMES HIT');

        try {
            $query = Game::with(['provider', 'categories']);

            if ($this->hasTableColumn('games', 'status')) {
                $query->where('status', 1);
            }

            if ($this->hasTableColumn('games', 'is_featured')) {
                $query->where('is_featured', 1)->orderByDesc('is_featured');
            }

            if ($this->hasTableColumn('games', 'views')) {
                $query->orderByDesc('views');
            } else {
                $query->orderByDesc('id');
            }

            $featuredGames = $query->limit(12)->get()->map(function ($game) {
                return $this->normalizeGame($game);
            })->values();

            if ($featuredGames->isEmpty()) {
                $featuredGames = collect($this->fallbackFeaturedGames());
            }

            return response()->json([
                'featured_games' => $featuredGames,
                'featuredGames' => $featuredGames,
                'games' => $featuredGames,
                'data' => $featuredGames,
            ], 200);
        } catch (Throwable $e) {
            Log::error('GameController@featured failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            $fallback = collect($this->fallbackFeaturedGames());

            return response()->json([
                'featured_games' => $fallback,
                'featuredGames' => $fallback,
                'games' => $fallback,
                'data' => $fallback,
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
                'change_free',
                'history_detail',
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

            $gameQuery = Game::query();

            if ($this->hasTableColumn('games', 'status')) {
                $gameQuery->where('status', 1);
            }

            $game = $gameQuery->where('game_code', $tokenOpen['game'])->first();

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

            if ($action === 'change_free') {
                return method_exists($controller, 'change_free')
                    ? $controller->change_free($request, $token)
                    : $this->emptyRuntimeSuccess('Change free success');
            }

            if ($action === 'history_detail') {
                return method_exists($controller, 'history_detail')
                    ? $controller->history_detail($request, $token)
                    : $this->emptyRuntimeSuccess('History detail success');
            }

            if ($action === 'buy') {
                return method_exists($controller, 'buy')
                    ? $controller->buy($request, $token)
                    : $this->emptyRuntimeSuccess('Buy success');
            }

            if ($action === 'logs') {
                return method_exists($controller, 'logs')
                    ? $controller->logs($token)
                    : $this->emptyRuntimeSuccess('Logs success');
            }

            if ($action === 'save') {
                return method_exists($controller, 'save')
                    ? $controller->save($request, $token)
                    : $this->emptyRuntimeSuccess('Save success');
            }

            if ($action === 'histories') {
                return method_exists($controller, 'histories')
                    ? $controller->histories($token)
                    : $this->emptyRuntimeSuccess('Histories success');
            }

            if ($action === 'collect') {
                return method_exists($controller, 'collect')
                    ? $controller->collect($token)
                    : $this->emptyRuntimeSuccess('Collect success');
            }

            if ($action === 'gamble') {
                return method_exists($controller, 'gamble')
                    ? $controller->gamble($request, $token)
                    : $this->emptyRuntimeSuccess('Gamble success');
            }

            if ($action === 'linenum') {
                return method_exists($controller, 'linenum')
                    ? $controller->linenum($request, $token)
                    : $this->emptyRuntimeSuccess('Linenum success');
            }

            return match ($action) {
                'session' => $controller->session($token),
                'spin' => $controller->spin($request, $token),
                'freenum' => $controller->freenum($request, $token),
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

            $gameQuery = Game::with(['categories', 'provider']);

            if ($this->hasTableColumn('games', 'status')) {
                $gameQuery->where('status', 1);
            }

            $game = $gameQuery->find($id);

            if (!$game) {
                $fallbackGame = $this->fallbackSingleGame($id);

                $token = \Helper::MakeToken([
                    'id' => auth('api')->id(),
                    'game' => $fallbackGame['game_code'],
                ]);

                $baseUrl = rtrim(config('app.url') ?: $request->getSchemeAndHttpHost(), '/');
                $gameUrl = $baseUrl . '/originals/' . $fallbackGame['game_code'] . '/index.html?token=' . urlencode($token);

                return response()->json([
                    'game' => $fallbackGame,
                    'gameUrl' => $gameUrl,
                    'token' => $token,
                    'fallback' => true,
                ], 200);
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

            $baseUrl = rtrim(config('app.url') ?: $request->getSchemeAndHttpHost(), '/');
            $gameUrl = $baseUrl . '/originals/' . $game->game_code . '/index.html?token=' . urlencode($token);

            return response()->json([
                'game' => $this->normalizeGame($game),
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

            if ($search !== '') {
                $searchable = array_filter([
                    $this->hasTableColumn('games', 'game_code') ? 'game_code' : null,
                    $this->hasTableColumn('games', 'game_name') ? 'game_name' : null,
                    $this->hasTableColumn('games', 'name') ? 'name' : null,
                    $this->hasTableColumn('games', 'description') ? 'description' : null,
                    $this->hasTableColumn('games', 'distribution') ? 'distribution' : null,
                    $this->hasTableColumn('games', 'slug') ? 'slug' : null,
                ]);

                if (!empty($searchable)) {
                    $query->where(function ($q) use ($searchable, $search) {
                        foreach (array_values($searchable) as $index => $column) {
                            if ($index === 0) {
                                $q->where($column, 'like', "%{$search}%");
                            } else {
                                $q->orWhere($column, 'like', "%{$search}%");
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

            $games = $query->paginate(12)->appends($request->query());

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