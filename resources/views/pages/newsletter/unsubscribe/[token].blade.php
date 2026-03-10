<?php

use App\Models\NewsletterSubscription;
use Illuminate\View\View;
use Spatie\ResponseCache\Middlewares\DoNotCacheResponse;
use function Laravel\Folio\{middleware, name, render};

name('newsletter.unsubscribe');
middleware([DoNotCacheResponse::class]);

render(function (View $view, string $token) {
    $subscription = NewsletterSubscription::where('token', $token)->firstOrFail();

    if (!$subscription->isActive()) {
        return $view->with('message', 'Diese E-Mail-Adresse wurde bereits vom Newsletter abgemeldet.');
    }

    $subscription->unsubscribe();

    return $view->with('message', 'Du wurdest erfolgreich vom Newsletter abgemeldet.');
});

?>

@extends ('layouts.app')

@section ('title', 'Newsletter abgemeldet – Männerkreis Niederbayern/ Straubing')
@section ('meta_description', 'Du hast dich erfolgreich von unserem Newsletter abgemeldet.')
@section ('robots', 'noindex, nofollow')

@section ('content')
  <section class="section section--large">
    <div class="container--narrow container">
      <div class="error-page">
        <div class="error-page__content">
          <h1 class="error-page__title">Newsletter abgemeldet</h1>
          <p class="error-page__text">{{ $message }}</p>
          <div class="error-page__actions">
            <a href="{{ route('home') }}" class="btn btn--primary"
              >Zurück zur Startseite</a
            >
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
