<?php

declare(strict_types=1);

use App\Http\Controllers\EventController;
use App\Http\Controllers\LlmsController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SocialiteController;
use App\Http\Controllers\TestimonialSubmissionController;
use Illuminate\Support\Facades\Route;
use Spatie\ResponseCache\Middlewares\DoNotCacheResponse;

Route::get('/llms.txt', [LlmsController::class, 'show'])->name('llms.txt');

Route::controller(PageController::class)->group(function (): void {
    Route::get('/', 'home')->name('home');
});

Route::redirect('/home', '/', 301);

Route::controller(EventController::class)->group(function (): void {
    Route::get('/event', 'showNext')->name('event.show');
    Route::get('/event/{slug}', 'show')->name('event.show.slug');
});

Route::redirect('/events', '/event', 301);
Route::redirect('/events/{slug}', '/event/{slug}', 301);

Route::get('/teile-deine-erfahrung', [TestimonialSubmissionController::class, 'show'])->name('testimonial.form');

Route::get('/newsletter/unsubscribe/{token}', [NewsletterController::class, 'unsubscribe'])
    ->middleware(DoNotCacheResponse::class)
    ->name('newsletter.unsubscribe');

Route::middleware(DoNotCacheResponse::class)->group(function (): void {
    Route::get('/auth/{provider}/redirect', [SocialiteController::class, 'redirect'])
        ->name('socialite.redirect');
    Route::get('/auth/{provider}/callback', [SocialiteController::class, 'callback'])
        ->name('socialite.callback');
});

// Dynamic pages (must be last to avoid conflicts)
Route::get('/{slug}', [PageController::class, 'show'])->name('page.show');
