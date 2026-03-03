<?php

declare(strict_types=1);

use App\Http\Controllers\EventController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\TestimonialSubmissionController;
use Illuminate\Support\Facades\Route;

Route::post('/event/register', [EventController::class, 'register'])->name('event.register');
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');
Route::post('/testimonial/submit', [TestimonialSubmissionController::class, 'submit'])->name('testimonial.submit');

