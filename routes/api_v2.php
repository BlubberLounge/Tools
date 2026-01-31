<?php

use App\Http\Controllers\Api\v1\UtillityController;
use App\Http\Controllers\Api\v2\DartEngineController;
use App\Http\Controllers\Api\v2\FeedbackController;
use App\Http\Controllers\Api\v2\NotificationController;
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

    // Push Notifications
    Route::post('push/subscribe', [NotificationController::class, 'subscribe']);
    Route::post('push/unsubscribe', [NotificationController::class, 'unsubscribe']);
});

// Public route for VAPID public key (no auth required)
Route::get('push/vapid-public-key', [NotificationController::class, 'vapidPublicKey']);
