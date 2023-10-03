<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\v1\UserController;
use App\Http\Controllers\Api\v1\DartGameController;
use App\Http\Controllers\Api\v1\DartThrowController;
use App\Http\Controllers\Api\v1\DartExpectationDataController;
use App\Http\Controllers\Api\v1\NotificationController;

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
    });

    Route::apiResource('user', UserController::class)
        ->only(['show']);


    // route: /dart/*
    // name: dart.*
    Route::prefix('dart')->group(function () {
        Route::put('/updatePlace/{dartGame}/{user}', [DartGameController::class, 'updatePlace']);
        Route::get('/showThrows/{dartGame}', [DartGameController::class, 'showThrows']);
        Route::get('/getPlayerStatus/{dartGame}', [DartGameController::class, 'getPlayerStatus']);
        Route::delete('/destroyPlayer/{dartGame}/user/{user}', [DartGameController::class, 'destroyPlayer']);
        Route::put('/{dartGame}/accept', [DartGameController::class, 'accept']);
        Route::put('/{dartGame}/decline', [DartGameController::class, 'decline']);

        // local and development only
        if(App::environment(['local', 'development']))
            Route::apiResource('expectationData', DartExpectationDataController::class)
                ->only(['index', 'store'])
                ->parameter('expectationData', 'dartExpectationData'); // expectationData to dartExpectationData for currect auto-mapping;
    });
    Route::apiResource('dart', DartGameController::class)
        ->only(['show', 'update'])
        ->parameter('dart', 'dartGame'); // dart to dartGame for currect auto-mapping;


    Route::apiResource('throw', DartThrowController::class)
        ->only(['store'])
        ->parameter('throw', 'dartThrow'); // throw to dartThrow for currect auto-mapping;


    Route::resource('notification', NotificationController::class)
        ->only(['index', 'show', 'update']);
});
