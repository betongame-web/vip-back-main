<?php

use App\Models\Game;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

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
Route::get('clear', function() {
    Artisan::command('clear', function () {
        Artisan::call('optimize:clear');
       return back();
    });

    return back();
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

/// SOCIAL
include_once(__DIR__ . '/groups/auth/social.php');

// APP
include_once(__DIR__ . '/groups/layouts/app.php');
