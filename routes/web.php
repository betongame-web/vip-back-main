<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return response()->json([
        'ok' => true,
        'service' => 'ViperPro Backend API',
        'status' => 'running',
        'health' => '/healthz.txt',
        'admin' => '/admin',
        'api_base' => '/api',
    ], 200);
});

Route::get('loadinggame', function () {
    return response('OK', 200);
});

Route::get('test', [\App\Http\Controllers\Provider\VibraController::class, 'start']);

Route::get('clear', function () {
    Artisan::command('clear', function () {
        Artisan::call('optimize:clear');
        return back();
    });

    return back();
});

Route::get('/__inspect', function (Request $request) {
    abort_unless($request->query('token') === env('DEBUG_INSPECT_TOKEN'), 404);

    $vgamesRoutes = collect(app('router')->getRoutes()->getRoutes())
        ->map(function ($route) {
            return [
                'methods' => $route->methods(),
                'uri' => $route->uri(),
                'name' => $route->getName(),
            ];
        })
        ->filter(function ($route) {
            return str_contains($route['uri'], 'api/vgames');
        })
        ->values();

    $settingsColumns = Schema::hasTable('settings')
        ? Schema::getColumnListing('settings')
        : [];

    $walletsColumns = Schema::hasTable('wallets')
        ? Schema::getColumnListing('wallets')
        : [];

    $gamesColumns = Schema::hasTable('games')
        ? Schema::getColumnListing('games')
        : [];

    $providersColumns = Schema::hasTable('providers')
        ? Schema::getColumnListing('providers')
        : [];

    $walletSelect = array_values(array_intersect([
        'id',
        'user_id',
        'balance',
        'bonus_balance',
        'withdrawable_balance',
        'total_balance',
        'balance_bonus',
        'balance_withdrawal',
        'status',
        'currency',
        'symbol',
    ], $walletsColumns));

    $gamesSelect = array_values(array_intersect([
        'id',
        'provider_id',
        'game_name',
        'name',
        'game_code',
        'distribution',
        'status',
        'is_featured',
        'views',
    ], $gamesColumns));

    return response()->json([
        'app_env' => config('app.env'),
        'app_url' => config('app.url'),
        'routes_file_check' => 'vgames-post-debug-v2',

        'tables' => DB::select("
            SELECT tablename
            FROM pg_tables
            WHERE schemaname = 'public'
            ORDER BY tablename
        "),

        'latest_migrations' => Schema::hasTable('migrations')
            ? DB::table('migrations')->orderByDesc('id')->limit(30)->get()
            : [],

        'vgames_routes' => $vgamesRoutes,

        'settings_columns' => $settingsColumns,
        'wallets_columns' => $walletsColumns,
        'games_columns' => $gamesColumns,
        'providers_columns' => $providersColumns,

        'settings_rows' => Schema::hasTable('settings')
            ? DB::table('settings')->limit(3)->get()
            : [],

        'wallets_rows' => Schema::hasTable('wallets')
            ? (count($walletSelect)
                ? DB::table('wallets')->select($walletSelect)->limit(5)->get()
                : DB::table('wallets')->limit(5)->get())
            : [],

        'games_rows' => Schema::hasTable('games')
            ? (count($gamesSelect)
                ? DB::table('games')->select($gamesSelect)->limit(10)->get()
                : DB::table('games')->limit(10)->get())
            : [],

        'providers_rows' => Schema::hasTable('providers')
            ? DB::table('providers')->limit(10)->get()
            : [],
    ]);
});

// GAMES PROVIDER
include_once(__DIR__ . '/groups/provider/games.php');
include_once(__DIR__ . '/groups/provider/vibra.php');
include_once(__DIR__ . '/groups/provider/kagaming.php');
include_once(__DIR__ . '/groups/provider/salsa.php');

// GATEWAYS
include_once(__DIR__ . '/groups/gateways/bspay.php');
include_once(__DIR__ . '/groups/gateways/stripe.php');
include_once(__DIR__ . '/groups/gateways/suitpay.php');

// SOCIAL
include_once(__DIR__ . '/groups/auth/social.php');

// APP
include_once(__DIR__ . '/groups/layouts/app.php');