<?php

declare(strict_types=1);

use App\Http\Controllers\Ai\EventManagementController;
use App\Http\Controllers\Ai\GeneralSettingsManagementController;
use App\Http\Controllers\Ai\NewsletterManagementController;
use App\Http\Controllers\Ai\PageManagementController;
use App\Http\Controllers\Ai\SiteContextController;
use App\Http\Controllers\Ai\TestimonialManagementController;
use App\Http\Controllers\BreathingController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\LlmsController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SocialiteController;
use App\Http\Controllers\TestimonialSubmissionController;
use App\Http\Middleware\EnsureAiAccess;
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

    Route::get('/event', [EventController::class, 'showNext'])->name('event.show');
    Route::get('/event/{slug}', [EventController::class, 'show'])->name('event.show.slug');
    Route::redirect('/events', '/event', 301);
    Route::redirect('/events/{slug}', '/event/{slug}', 301);

    Route::get('/teile-deine-erfahrung', [TestimonialSubmissionController::class, 'show'])->name('testimonial.form');

    Route::get('/atemuebung', [BreathingController::class, 'show'])->name('breathing.show');

    // Dynamic CMS pages (must be last to avoid conflicts)
    Route::get('/{slug}', [PageController::class, 'show'])->name('page.show');
});


Route::prefix('api/ai')
    ->withoutMiddleware([PreventRequestForgery::class])
    ->middleware([EnsureAiAccess::class, DoNotCacheResponse::class, 'throttle:60,1'])
    ->group(function (): void {
        Route::get('/site-context', SiteContextController::class)->name('ai.site-context');

        Route::get('/events', [EventManagementController::class, 'index'])->name('ai.events.index');
        Route::post('/events/plan', [EventManagementController::class, 'plan'])->name('ai.events.plan');
        Route::post('/events', [EventManagementController::class, 'store'])->name('ai.events.store');
        Route::get('/events/{event}', [EventManagementController::class, 'show'])->name('ai.events.show');
        Route::patch('/events/{event}', [EventManagementController::class, 'update'])->name('ai.events.update');
        Route::post('/events/{event}/publish', [EventManagementController::class, 'publish'])->name('ai.events.publish');

        Route::get('/pages', [PageManagementController::class, 'index'])->name('ai.pages.index');
        Route::post('/pages/generate', [PageManagementController::class, 'generate'])->name('ai.pages.generate');
        Route::get('/pages/{page}', [PageManagementController::class, 'show'])->name('ai.pages.show');
        Route::patch('/pages/{page}', [PageManagementController::class, 'update'])->name('ai.pages.update');
        Route::patch('/pages/{page}/blocks', [PageManagementController::class, 'updateBlocks'])->name('ai.pages.blocks.update');
        Route::post('/pages/{page}/publish', [PageManagementController::class, 'publish'])->name('ai.pages.publish');

        Route::post('/newsletters/generate', [NewsletterManagementController::class, 'generate'])->name('ai.newsletters.generate');
        Route::post('/newsletters/{newsletter}/preview', [NewsletterManagementController::class, 'preview'])->name('ai.newsletters.preview');
        Route::post('/newsletters/{newsletter}/send', [NewsletterManagementController::class, 'send'])->name('ai.newsletters.send');

        Route::get('/testimonials/pending', [TestimonialManagementController::class, 'pending'])->name('ai.testimonials.pending');
        Route::post('/testimonials/{testimonial}/publish', [TestimonialManagementController::class, 'publish'])->name('ai.testimonials.publish');
        Route::post('/testimonials/{testimonial}/reject', [TestimonialManagementController::class, 'reject'])->name('ai.testimonials.reject');

        Route::get('/settings/general', [GeneralSettingsManagementController::class, 'show'])->name('ai.settings.general.show');
        Route::patch('/settings/general', [GeneralSettingsManagementController::class, 'update'])->name('ai.settings.general.update');
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
