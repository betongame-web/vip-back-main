<?php

use App\Http\Controllers\Api\Games\GameController;
use Illuminate\Support\Facades\Route;

Route::prefix('games')->group(function () {
    Route::get('all', [GameController::class, 'index']);
    Route::get('single/{id}', [GameController::class, 'show']);
    Route::post('favorite/{id}', [GameController::class, 'toggleFavorite'])->middleware('auth.jwt');
    Route::post('like/{id}', [GameController::class, 'toggleLike'])->middleware('auth.jwt');
});

Route::prefix('featured')->group(function () {
    Route::get('games', [GameController::class, 'featured']);
});

Route::prefix('vgames')->group(function () {
    Route::get('pricing', [GameController::class, 'pricing']);
    Route::get('checkfree', [GameController::class, 'checkFree']);
    Route::get('freecredit', [GameController::class, 'freeCredit']);
    Route::get('checklucky', [GameController::class, 'checkLucky']);
    Route::get('luckywheel', [GameController::class, 'luckyWheel']);
    Route::get('history/{id}', [GameController::class, 'historyDetail']);
    Route::any('{token}/{action}', [GameController::class, 'sourceProvider']);
});

Route::prefix('casinos')->group(function () {
    Route::get('games', [GameController::class, 'allGames']);
});
