<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

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
use App\Http\Controllers\MailTrackerController;

use App\Mail\InvitationMail;
use App\Models\Invitation;
use App\Enums\InvitationStatus;

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
    // return view('login');
    return redirect('login');
})->name('index');

Route::get('/register/request', [InvitationController::class, 'request'])->name('register.request');
Route::put('/register/request/store', [InvitationController::class, 'store'])->name('register.request.store');
Route::get('/register/{invitationToken}', [RegisterController::class, 'showRegistrationForm'])
    ->whereUuid('invitationToken')
    ->middleware('invitation.verifyToken')
    ->name('register.register');
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
// Route::middleware(['auth'])->group(function ()
// {
//     Route::get('/home', [HomeController::class, 'index'])->name('home');
// });

Route::middleware(['auth', 'verified'])->group(function ()
{
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    // Route::get('/callback', [HomeController::class, 'callback'])->name('callback');
    Route::view('/battery', 'battery.index')->name('battery');
    Route::resource('/hookah', HookahController::class);

    // Routes with no specific controller
    Route::get('/moving-average', [HomeController::class, 'ShowMovingAverage'])
        ->name('show-moving-average');
        // ->middleware('permission:view.moving.average');
    Route::get('/airsoft-calculator', [HomeController::class, 'ShowAirsoftCalculator'])
        ->name('show-airsoft-calculator');
    Route::get('/iec7064', [HomeController::class, 'ShowIEC7064'])
        ->name('show-iec7064');

    // route: /dart/*
    // name: dart.*
    Route::prefix('dart')->group(function () {
        Route::name('dart.')->group(function ()
        {
            Route::get('/game/live', [DartGameController::class, 'showLive'])
                ->name('game.live');
            Route::resource('/game', DartGameController::class)
                ->parameter('game', 'dartGame'); // dart to dartGame for currect auto-mapping

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
            Route::get('/settings', [UserController::class, 'showSettings'])->name('settings');
            Route::put('/language/update', [UserController::class, 'languageUpdate'])->name('language-update');
            Route::get('/{user}/editImage', [UserController::class, 'editImage'])->name('edit-image');
        });
    });
    Route::resource('/user', UserController::class);

    // route: /invitation/*
    // name: invitation.*
    Route::prefix('invitation')->group(function () {
        Route::name('invitation.')->group(function ()
        {
            Route::post('/approve/{invitation}', [InvitationController::class, 'approve'])->name('approve');
            Route::post('/denie/{invitation}', [InvitationController::class, 'denie'])->name('denie');
        });
    });
    Route::resource('/invitation', InvitationController::class)
        ->except(['destroy', 'request']);

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
        // Mailables Design Testing
        Route::get('/mail/invitation', function(){
            $invitation = new Invitation();
            $invitation->status = InvitationStatus::APPROVED;
            $invitation->token = Str::orderedUuid();
            $invitation->expires_at = now();

            return (new InvitationMail($invitation))
                ->render();
        });
    }

    // ADMIN routes
    Route::group(['middleware' => ['level:6']], function ()
    {
        Route::get('/audit-log', [AuditLogController::class, 'index'])->name('audit-log.index');
    });
});


/*
 * Email tracker routes
 */
Route::get('/t/{notification}/t.gif', [MailTrackerController::class, 'handle'])->name('mail-tracker.t');
