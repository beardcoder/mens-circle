<?php

declare(strict_types=1);

use App\Http\Controllers\EventController;
use App\Http\Controllers\LlmsController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SocialiteController;
use App\Http\Controllers\TestimonialSubmissionController;
use Illuminate\Support\Facades\Route;
use Spatie\Health\Http\Controllers\HealthCheckJsonResultsController;

Route::get('/llms.txt', [LlmsController::class, 'show'])->name('llms.txt');

Route::controller(PageController::class)->group(function (): void {
    Route::get('/', 'home')->name('home');
});

Route::controller(EventController::class)->group(function (): void {
    Route::get('/event', 'showNext')->name('event.show');
    Route::get('/event/{slug}', 'show')->name('event.show.slug');
    Route::post('/event/register', 'register')->name('event.register');
});

Route::redirect('/events', '/event', 301);
Route::redirect('/events/{slug}', '/event/{slug}', 301);

Route::controller(TestimonialSubmissionController::class)->group(function (): void {
    Route::get('/teile-deine-erfahrung', 'show')->name('testimonial.form');
    Route::post('/testimonial/submit', 'submit')->name('testimonial.submit');
});

Route::controller(NewsletterController::class)->group(function (): void {
    Route::post('/newsletter/subscribe', 'subscribe')->name('newsletter.subscribe');
    Route::get('/newsletter/unsubscribe/{token}', 'unsubscribe')->name('newsletter.unsubscribe');
});

Route::get('/auth/{provider}/redirect', [SocialiteController::class, 'redirect'])
    ->name('socialite.redirect');
Route::get('/auth/{provider}/callback', [SocialiteController::class, 'callback'])
    ->name('socialite.callback');

// Health check routes
Route::get('/health', HealthCheckJsonResultsController::class)->name('health');

// Dynamic pages (must be last to avoid conflicts)
Route::get('/{slug}', [PageController::class, 'show'])->name('page.show');
