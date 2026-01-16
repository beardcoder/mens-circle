<?php

declare(strict_types=1);

use App\Http\Controllers\AiDiscoveryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| AI Discovery API Routes
|--------------------------------------------------------------------------
|
| Public, read-only endpoints for AI systems to discover and understand
| site structure, content, and events. All responses are JSON-formatted
| and respect publishing workflows.
|
*/

Route::prefix('ai')->name('ai.')->group(function () {
    Route::get('site', [AiDiscoveryController::class, 'site'])->name('site');
    Route::get('pages', [AiDiscoveryController::class, 'pages'])->name('pages');
    Route::get('events', [AiDiscoveryController::class, 'events'])->name('events');
});
