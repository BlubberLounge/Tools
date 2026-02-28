<?php

use App\Http\Controllers\Api\v1\UtillityController;
use App\Http\Controllers\Api\v2\DartEngineController;
use App\Http\Controllers\Api\v2\DartTeamController;
use App\Http\Controllers\Api\v2\DartTournamentController;
use App\Http\Controllers\Api\v2\DartPlayerInvitationController;
use App\Http\Controllers\Api\v2\DartPushController;
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
    Route::post('dart/{game}/user/{user}/undo', [DartEngineController::class, 'undoThrow']);
    Route::patch('dart/{game}/status', [DartEngineController::class, 'updateStatus']);
    Route::get('dart/{game}/detail', [DartEngineController::class, 'detail']);

    // Team management (scoped to a game)
    Route::prefix('dart/{game}/teams')->group(function () {
        Route::get('/', [DartTeamController::class, 'index']);
        Route::post('/', [DartTeamController::class, 'store']);
        Route::put('/{team}', [DartTeamController::class, 'update']);
        Route::delete('/{team}', [DartTeamController::class, 'destroy']);
        Route::post('/{team}/players', [DartTeamController::class, 'addPlayer']);
        Route::delete('/{team}/players/{user}', [DartTeamController::class, 'removePlayer']);
        Route::put('/{team}/players', [DartTeamController::class, 'setPlayers']);
    });

    // Tournament management
    Route::prefix('tournaments')->group(function () {
        Route::get('/', [DartTournamentController::class, 'index']);
        Route::post('/', [DartTournamentController::class, 'store']);
        Route::get('/{tournament}', [DartTournamentController::class, 'show']);
        Route::patch('/{tournament}', [DartTournamentController::class, 'update']);
        Route::delete('/{tournament}', [DartTournamentController::class, 'destroy']);

        // Participants
        Route::post('/{tournament}/participants', [DartTournamentController::class, 'addParticipants']);
        Route::delete('/{tournament}/participants/{user}', [DartTournamentController::class, 'removeParticipant']);

        // Lifecycle
        Route::post('/{tournament}/seed', [DartTournamentController::class, 'seed']);
        Route::post('/{tournament}/start', [DartTournamentController::class, 'start']);
        Route::patch('/{tournament}/status', [DartTournamentController::class, 'updateStatus']);

        // Matches & Rounds
        Route::get('/{tournament}/matches', [DartTournamentController::class, 'matches']);
        Route::get('/{tournament}/rounds/{round}', [DartTournamentController::class, 'round']);
        Route::post('/{tournament}/matches/{match}/start', [DartTournamentController::class, 'startMatch']);
        Route::post('/{tournament}/matches/{match}/complete', [DartTournamentController::class, 'completeMatch']);

        // Standings
        Route::get('/{tournament}/standings', [DartTournamentController::class, 'standings']);
    });

    // Player Invitations (offline → BlubberLounge account)
    Route::get('dart/invitations', [DartPlayerInvitationController::class, 'index']);
    Route::post('dart/invitations', [DartPlayerInvitationController::class, 'store']);
    Route::get('dart/invitations/{invitation}/status', [DartPlayerInvitationController::class, 'status']);

    // Feedback from DartApp
    Route::post('feedback', [FeedbackController::class, 'store']);

    // Push Notifications
    Route::post('push/subscribe', [NotificationController::class, 'subscribe']);
    Route::post('push/unsubscribe', [NotificationController::class, 'unsubscribe']);
});

// Public route for VAPID public key (no auth required)
Route::get('push/vapid-public-key', [NotificationController::class, 'vapidPublicKey']);

// Dart App Push Notifications (anonymous, no auth required)
Route::prefix('dart-push')->group(function () {
    Route::get('vapid-key', [DartPushController::class, 'vapidPublicKey']);
    Route::post('subscribe', [DartPushController::class, 'subscribe']);
    Route::post('unsubscribe', [DartPushController::class, 'unsubscribe']);
});

// Admin route for sending notifications (should be protected in production)
Route::post('dart-push/send', [DartPushController::class, 'sendUpdateNotification']);

// Dart App Feedback (anonymous, no auth required)
Route::post('dart-feedback', [FeedbackController::class, 'storeAnonymous']);
