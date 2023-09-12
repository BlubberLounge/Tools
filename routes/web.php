<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\BatteryController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\HookahController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DartController;
use App\Http\Controllers\DartGameController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\FAQController;
use App\Http\Controllers\FeedbackController;

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

Route::get('/register/request', [InvitationController::class, 'request'])->name('register.request');
Route::put('/register/request/store', [InvitationController::class, 'store'])->name('register.request.store');
Route::get('/register/{registrationID}', [RegisterController::class, 'showRegistrationForm'])->name('register.register');
Route::post('/register', [RegisterController::class, 'register'])->name('register');

// https://github.com/laravel/ui/blob/4.x/src/AuthRouteMethods.php
Auth::routes(['verify' => true, 'register' => false]);

// Route::get('/auth/redirect', function () {
//     return Socialite::driver('google')->redirect();
// })->name('auth.google.redirect');

// Route::get('/auth/callback', function () {
//     $user = Socialite::driver('google')->user();
//     dd($user);

//     // $user->token
// })->name('auth.google.callback');


/*
 * protected routes
 */
Route::middleware(['auth', 'verified'])->group(function ()
{
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/battery', [BatteryController::class, 'index'])->name('battery');
    Route::resource('/hookah', HookahController::class);

    // Routes with no specific controller
    Route::get('/moving-average', [HomeController::class, 'ShowMovingAverage'])->name('show-moving-average');

    // route: /dart/*
    // name: dart.*
    Route::prefix('dart')->group(function () {
        Route::name('dart.')->group(function ()
        {
            Route::resource('/game', DartGameController::class)->parameter('game', 'dartGame'); // dart to dartGame for currect auto-mapping
            Route::get('/game/live', [DartGameController::class, 'showLive'])->name('game.live');

            Route::get('/info', [DartController::class, 'showInfo'])->name('show-info');
            Route::get('/checkouts/{score?}', [DartController::class, 'showCheckoutCalculator'])->name('show-checkout-calculator');
            Route::get('/playground', [DartController::class, 'showPlayground'])->name('show-playground');
        });
    });
    Route::resource('/dart', DartController::class);

    // route: /user/*
    // name: user.*
    Route::prefix('user')->group(function () {
        Route::name('user.')->group(function ()
        {
            Route::put('/language/update', [UserController::class, 'languageUpdate'])->name('language-update');
            Route::get('/{user}/editImage', [UserController::class, 'editImage'])->name('edit-image');
        });
    });
    Route::resource('/user', UserController::class);

    Route::get('/device', [DeviceController::class, 'index'])->name('device.index');
    Route::resource('faq', FAQController::class)
        ->except('destroy');
    Route::resource('feedback', FeedbackController::class)
        ->except('destroy');

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
        Route::get('/audit-log', [AuditLogController::class, 'index'])->name('audit-log.index');
    });
});
