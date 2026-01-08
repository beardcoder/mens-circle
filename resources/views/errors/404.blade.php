@extends('layouts.app')

@section('title', 'Seite nicht gefunden – Männerkreis Niederbayern/ Straubing')
@section('meta_description', 'Die gesuchte Seite existiert nicht oder wurde verschoben.')
@section('robots', 'noindex, nofollow')

@section('content')
    <section class="section section--large">
        <div class="container container--narrow">
            <div class="error-page">
                <div class="error-page__content">
                    <p class="error-page__code" aria-hidden="true">404</p>
                    <h1 class="error-page__title">Seite nicht gefunden</h1>
                    <p class="error-page__text">
                        Die gesuchte Seite existiert leider nicht oder wurde verschoben.
                    </p>
                    <div class="error-page__actions">
                        <a href="{{ route('home') }}" class="btn btn--primary">
                            Zur Startseite
                        </a>
                        @if($hasNextEvent ?? false)
                            <a href="{{ route('event.show') }}" class="btn btn--secondary">
                                Zum nächsten Termin
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
