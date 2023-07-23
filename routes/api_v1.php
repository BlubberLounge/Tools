<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\v1\UserController;


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

// Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', 'verified'])->group(function ()
{

    Route::apiResource('user', UserController::class)
        ->only(['show']);
    // route: /user/*
    // name: user.*
    Route::prefix('user')->group(function () {
        Route::name('user.')->group(function ()
        {
            Route::get('/search/{user}', [UserController::class, 'search'])->name('search');
        });
    });
});
