<?php

use App\Http\Controllers\Api\v1\UtillityController;
use App\Http\Controllers\Api\v2\DartEngineController;
use App\Http\Controllers\Api\v2\FeedbackController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/ping', [UtillityController::class, 'ping']);

Route::middleware(['auth:sanctum'])->group(function ()
{
    Route::get('dart/players', [DartEngineController::class, 'availablePlayers']);
    Route::post('dart/new', [DartEngineController::class, 'store']);
    Route::post('dart/user/{user}/endGames', [DartEngineController::class, 'endGamesByUser']);
    Route::get('dart/active', [DartEngineController::class, 'activeGameState']);
    Route::get('dart/past', [DartEngineController::class, 'pastGames']);
    Route::get('dart/{game}/state', [DartEngineController::class, 'state']);
    Route::post('dart/{game}/start', [DartEngineController::class, 'start']);
    Route::post('dart/{game}/user/{user}/throw', [DartEngineController::class, 'submitThrow']);

    // Feedback from DartApp
    Route::post('feedback', [FeedbackController::class, 'store']);
});
