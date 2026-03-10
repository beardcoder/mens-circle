<?php

declare(strict_types=1);

use App\Http\Controllers\LlmsController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SocialiteController;
use Illuminate\Support\Facades\Route;
use Spatie\ResponseCache\Middlewares\DoNotCacheResponse;

Route::get('/llms.txt', [LlmsController::class, 'show'])->name('llms.txt');

Route::get('/', [PageController::class, 'home'])->name('home');

Route::redirect('/home', '/', 301);
Route::redirect('/events', '/event', 301);
Route::redirect('/events/{slug}', '/event/{slug}', 301);

Route::middleware(DoNotCacheResponse::class)->group(function (): void {
    Route::get('/auth/{provider}/redirect', [SocialiteController::class, 'redirect'])
        ->name('socialite.redirect');
    Route::get('/auth/{provider}/callback', [SocialiteController::class, 'callback'])
        ->name('socialite.callback');
});

// Dynamic pages (must be last to avoid conflicts, excludes Folio-handled paths)
Route::get('/{slug}', [PageController::class, 'show'])
    ->name('page.show')
    ->where('slug', '^(?!event$|teile-deine-erfahrung$|newsletter/).*$');
