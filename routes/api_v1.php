<?php

use App\Http\Controllers\Api\v1\AcquaintanceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\v1\UserController;
use App\Http\Controllers\Api\v1\AppointmentController;
use App\Http\Controllers\Api\v1\DartGameController;
use App\Http\Controllers\Api\v1\DartThrowController;
use App\Http\Controllers\Api\v1\DartExpectationDataController;
use App\Http\Controllers\Api\v1\NotificationController;
use App\Http\Controllers\Api\v1\TimetableController;
use App\Http\Controllers\Api\v1\UserTimetableController;
use App\Http\Controllers\Api\v1\UtillityController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/ping', [UtillityController::class, 'ping']);

Route::middleware(['auth:sanctum', 'verified'])->group(function ()
{
    // route: /user/*
    // name: user.*
    Route::prefix('user')->group(function () {
        Route::get('/search/{user}', [UserController::class, 'search']);
        Route::get('/showDashboardData', [UserController::class, 'showDashboardData']);
        Route::get('/showPlaces', [UserController::class, 'showPlaces']);
        Route::get('/showPositions', [UserController::class, 'showPositions']);
        Route::get('/showDartActivity', [UserController::class, 'showDartActivity']);
        Route::get('/showGameTypes', [UserController::class, 'showGameTypes']);
        Route::get('/showThrowsByGame/{dartGame}', [UserController::class, 'showThrowsByGame']);
        Route::put('/updateSettings', [UserController::class, 'updateSettings']);
    });

    Route::apiResource('user', UserController::class)
        ->only(['show']);
        Route::apiResource('user.timetable', UserTimetableController::class)
        ->except(['store', 'update', 'destroy']);
    Route::get('/user/{user}/timetable/between/{dateFrom}/{dateTo}', [UserTimetableController::class, 'betweenDates'])->name('user.timetable.between');

    Route::apiResource('timetable', TimetableController::class)
        ->except(['destroy']);
    Route::get('/timetable/between/{dateFrom}/{dateTo}', [TimetableController::class, 'betweenDates'])->name('timetable.between');

    Route::apiResource('acquaintance', AcquaintanceController::class)
        ->except(['destroy']);
    Route::put('/acquaintance/byReceiver/{acquaintance}', [AcquaintanceController::class, 'updateByReceiver'])->name('user.timetable.updateByReceiver');
    Route::put('/acquaintance/byTransmitter/{acquaintance}', [AcquaintanceController::class, 'updateByTransmitter'])->name('user.timetable.updateByTransmitter');
    Route::put('/acquaintance/byReceiverOrTransmitter/{acquaintance}', [AcquaintanceController::class, 'updateByReceiverOrTransmitter'])->name('user.timetable.updateByReceiverOrTransmitter');


    // route: /dart/*
    // name: dart.*
    Route::prefix('dart')->group(function () {
        Route::put('/updatePlace/{dartGame}/{user}', [DartGameController::class, 'updatePlace']);
        Route::get('/showThrows/{dartGame}', [DartGameController::class, 'showThrows']);
        Route::get('/showThrows/{dartGame}/user/{user}', [DartGameController::class, 'showThrowsByUser']);
        Route::get('/getPlayerStatus/{dartGame}', [DartGameController::class, 'getPlayerStatus']);
        Route::delete('/destroyPlayer/{dartGame}/user/{user}', [DartGameController::class, 'destroyPlayer']);
        Route::put('/{dartGame}/accept', [DartGameController::class, 'accept']);
        Route::put('/{dartGame}/decline', [DartGameController::class, 'decline']);
        Route::post('/queue/add', [DartGameController::class, 'queueAdd']);
        Route::post('/queue/remove', [DartGameController::class, 'queueRemove']);

        // local and development only
        if(App::environment(['local', 'development']))
            Route::apiResource('expectationData', DartExpectationDataController::class)
                ->only(['index', 'store'])
                ->parameter('expectationData', 'dartExpectationData'); // expectationData to dartExpectationData for currect auto-mapping;
    });
    Route::apiResource('dart', DartGameController::class)
        ->only(['show', 'update'])
        ->parameter('dart', 'dartGame'); // dart to dartGame for currect auto-mapping;

    Route::apiResource('appointment', AppointmentController::class)
        ->only(['index']);

    Route::apiResource('throw', DartThrowController::class)
        ->only(['store'])
        ->parameter('throw', 'dartThrow'); // throw to dartThrow for currect auto-mapping;


    Route::prefix('notification')->group(function () {
        Route::post('/push', [NotificationController::class, 'push']);
    });
    Route::resource('notification', NotificationController::class)
        ->only(['index', 'show', 'update']);
});
