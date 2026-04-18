<?php

declare(strict_types=1);

use App\Http\Controllers\EventController;
use App\Http\Controllers\LlmsController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SocialiteController;
use App\Http\Controllers\TestimonialSubmissionController;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Spatie\ResponseCache\Middlewares\DoNotCacheResponse;

/*
|--------------------------------------------------------------------------
| Public cached routes (no session/cookie/CSRF overhead)
|--------------------------------------------------------------------------
|
| These routes serve public, anonymous content cached by Spatie Response
| Cache. Stripping session and cookie middleware eliminates ~150ms of
| unnecessary Redis round-trips on every cache hit. None of these
| controllers use sessions, flash data, or CSRF tokens.
|
*/
Route::withoutMiddleware([
    EncryptCookies::class,
    AddQueuedCookiesToResponse::class,
    StartSession::class,
    ShareErrorsFromSession::class,
    PreventRequestForgery::class,
])->group(function (): void {
    Route::get('/llms.txt', [LlmsController::class, 'show'])->name('llms.txt');

    Route::get('/', [PageController::class, 'home'])->name('home');
    Route::redirect('/home', '/', 301);

    Route::view('/atmung', 'breathing')->name('breathing');

    Route::get('/event', [EventController::class, 'showNext'])->name('event.show');
    Route::get('/event/{slug}', [EventController::class, 'show'])->name('event.show.slug');
    Route::redirect('/events', '/event', 301);
    Route::redirect('/events/{slug}', '/event/{slug}', 301);

    Route::get('/teile-deine-erfahrung', [TestimonialSubmissionController::class, 'show'])->name('testimonial.form');

    // Dynamic CMS pages (must be last to avoid conflicts)
    Route::get('/{slug}', [PageController::class, 'show'])->name('page.show');
});

/*
|--------------------------------------------------------------------------
| Routes requiring sessions (auth, newsletter)
|--------------------------------------------------------------------------
*/
Route::get('/newsletter/unsubscribe/{token}', [NewsletterController::class, 'unsubscribe'])
    ->middleware(DoNotCacheResponse::class)
    ->name('newsletter.unsubscribe');

Route::middleware(DoNotCacheResponse::class)->group(function (): void {
    Route::get('/auth/{provider}/redirect', [SocialiteController::class, 'redirect'])
        ->name('socialite.redirect');
    Route::get('/auth/{provider}/callback', [SocialiteController::class, 'callback'])
        ->name('socialite.callback');
});
