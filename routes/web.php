use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

Route::get('/__inspect', function (Request $request) {
    abort_unless($request->query('token') === env('DEBUG_INSPECT_TOKEN'), 404);

    $routes = collect(app('router')->getRoutes()->getRoutes())
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

    return response()->json([
        'app_env' => config('app.env'),
        'app_url' => config('app.url'),

        'tables' => DB::select("
            SELECT tablename
            FROM pg_tables
            WHERE schemaname = 'public'
            ORDER BY tablename
        "),

        'latest_migrations' => Schema::hasTable('migrations')
            ? DB::table('migrations')->orderByDesc('id')->limit(30)->get()
            : [],

        'vgames_routes' => $routes,

        'settings_columns' => Schema::hasTable('settings')
            ? Schema::getColumnListing('settings')
            : [],

        'wallets_columns' => Schema::hasTable('wallets')
            ? Schema::getColumnListing('wallets')
            : [],

        'games_columns' => Schema::hasTable('games')
            ? Schema::getColumnListing('games')
            : [],

        'providers_columns' => Schema::hasTable('providers')
            ? Schema::getColumnListing('providers')
            : [],

        'settings_rows' => Schema::hasTable('settings')
            ? DB::table('settings')->limit(3)->get()
            : [],

        'wallets_rows' => Schema::hasTable('wallets')
            ? DB::table('wallets')
                ->select([
                    'id',
                    'user_id',
                    'balance',
                    'bonus_balance',
                    'withdrawable_balance',
                    'total_balance',
                ])
                ->limit(5)
                ->get()
            : [],

        'games_rows' => Schema::hasTable('games')
            ? DB::table('games')
                ->select([
                    'id',
                    'provider_id',
                    'name',
                    'game_code',
                    'distribution',
                    'status',
                ])
                ->limit(10)
                ->get()
            : [],

        'providers_rows' => Schema::hasTable('providers')
            ? DB::table('providers')->limit(10)->get()
            : [],
    ]);
});
