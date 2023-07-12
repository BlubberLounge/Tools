<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\BatteryController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\HookahController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DartController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('index');
});

// https://github.com/laravel/ui/blob/4.x/src/AuthRouteMethods.php
// if (App::environment('local')) {
    Auth::routes(['verify' => true]);
// } else {
//     Auth::routes(['verify' => true, 'register' => false]);
// }

/*
 * email verification routes
 */
// Route::get('/email', function () {
//     return view('auth.verify');
// })->middleware('auth')->name('verification.notice');

// Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
//     $request->fulfill();

//     return redirect('/home');
// })->middleware(['auth'])->name('verification.verify');

// Route::post('/email/verification-notification', function (Request $request) {
//     $request->user()->sendEmailVerificationNotification();

//     return back()->with('message', 'Verification link sent!');
// })->middleware(['auth', 'throttle:6,1'])->name('verification.send');


/*
 * protected routes
 */
Route::middleware(['auth', 'verified'])->group(function ()
{
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/battery', [BatteryController::class, 'index'])->name('battery');
    Route::get('/dart', [DartController::class, 'gameIndex'])->name('dart.game.index');
    Route::get('/checkouts/dartboard', [DartController::class, 'showDartboard'])->name('dart.showDartboard');
    Route::get('/checkouts/{score?}', [DartController::class, 'showCheckout'])->name('dart.showCheckout');

    Route::resource('/hookah', HookahController::class);
    Route::resource('/user', UserController::class);

    /**
     * LOCAL only Routes
     */
    if (App::environment(['local', 'development']))
    {
        // Mail Design Testing
        Route::get('/mail', function(){
            $mail = new App\Mail\TestMail();
            return $mail->render();
        });
    }

    // ADMIN routes
    Route::group(['middleware' => ['level:5']], function ()
    {
        Route::get('/audit-log', [AuditLogController::class, 'index'])->name('auditLog');
    });
});
