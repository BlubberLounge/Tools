<?php

use App\Http\Controllers\Api\v1\UtillityController;
use App\Http\Controllers\Api\v2\DartEngineController;
use App\Http\Controllers\Api\v2\DartTeamController;
use App\Http\Controllers\Api\v2\DartTournamentController;
use App\Http\Controllers\Api\v2\DartPlayerInvitationController;
use App\Http\Controllers\Api\v2\DartLocalPlayerController;
use App\Http\Controllers\Api\v2\DartSettingsController;
use App\Http\Controllers\Api\v2\DartStatisticsController;
use App\Http\Controllers\Api\v2\DartHistoryController;
use App\Http\Controllers\Api\v2\AuthTokenExchangeController;
use App\Http\Controllers\Api\v2\DartPushController;
use App\Http\Controllers\Api\v2\AcquaintanceController;
use App\Http\Controllers\Api\v2\FeedbackController;
use App\Http\Controllers\Api\v2\NotificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/ping', [UtillityController::class, 'ping']);

Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function ()
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
    Route::post('dart/{game}/complete', [DartEngineController::class, 'complete']);

    // Local players (offline player profiles)
    Route::get('dart/players/local', [DartLocalPlayerController::class, 'index']);
    Route::post('dart/players/local', [DartLocalPlayerController::class, 'store']);
    Route::put('dart/players/local/{localPlayer}', [DartLocalPlayerController::class, 'update']);
    Route::delete('dart/players/local/{localPlayer}', [DartLocalPlayerController::class, 'destroy']);

    // Settings sync
    Route::get('dart/settings', [DartSettingsController::class, 'show']);
    Route::post('dart/settings', [DartSettingsController::class, 'upsert']);

    // Statistics & Leaderboard
    Route::get('dart/statistics/{user}', [DartStatisticsController::class, 'show']);
    Route::get('dart/leaderboard', [DartStatisticsController::class, 'leaderboard']);

    // Game history & bulk sync
    Route::get('dart/history', [DartHistoryController::class, 'index']);
    Route::post('dart/history/sync', [DartHistoryController::class, 'sync']);
    Route::get('dart/sync/status', [DartHistoryController::class, 'status']);

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

    // Acquaintances (QR code friend scanning)
    Route::get('dart/acquaintances/qr-token', [AcquaintanceController::class, 'qrToken']);
    Route::get('dart/acquaintances', [AcquaintanceController::class, 'index']);
    Route::post('dart/acquaintances', [AcquaintanceController::class, 'store']);

    // Logout (revoke current Sanctum token)
    Route::post('auth/logout', function (Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    });

    // Feedback from DartApp
    Route::post('feedback', [FeedbackController::class, 'store']);

    // Push Notifications
    Route::post('push/subscribe', [NotificationController::class, 'subscribe']);
    Route::post('push/unsubscribe', [NotificationController::class, 'unsubscribe']);
});

// Public route for VAPID public key (no auth required)
Route::get('push/vapid-public-key', [NotificationController::class, 'vapidPublicKey']);

// Dart App Push Notifications (anonymous, rate limited)
Route::prefix('dart-push')->middleware('throttle:10,1')->group(function () {
    Route::get('vapid-key', [DartPushController::class, 'vapidPublicKey']);
    Route::post('subscribe', [DartPushController::class, 'subscribe']);
    Route::post('unsubscribe', [DartPushController::class, 'unsubscribe']);
});

// Admin route for sending notifications
Route::post('dart-push/send', [DartPushController::class, 'sendUpdateNotification'])
    ->middleware(['auth:sanctum', 'throttle:5,1']);

// Dart App Feedback (anonymous, rate limited)
Route::post('dart-feedback', [FeedbackController::class, 'storeAnonymous'])
    ->middleware('throttle:5,1');

// Token exchange: OAuth access token → Sanctum token (unauthenticated - this IS the login flow)
Route::post('auth/token-exchange', [AuthTokenExchangeController::class, 'exchange'])
    ->middleware('throttle:10,1');
