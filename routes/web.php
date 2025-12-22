<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/event', [EventController::class, 'showNext'])->name('event.show');
Route::get('/event/{slug}', [EventController::class, 'show'])->name('event.show.slug');
Route::post('/event/register', [EventController::class, 'register'])->name('event.register');
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');
Route::get('/newsletter/unsubscribe/{token}', [NewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe');

// SEO
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', function () {
    $robots = "User-agent: *\n";
    $robots .= "Allow: /\n";
    $robots .= 'Sitemap: '.route('sitemap')."\n";

    return response($robots, 200)->header('Content-Type', 'text/plain');
})->name('robots');

// Dynamic pages (must be last to avoid conflicts)
Route::get('/{slug}', [PageController::class, 'show'])->name('page.show');
