@extends('layouts.app')

@section('title', 'Serverfehler – Männerkreis Niederbayern/ Straubing')
@section('meta_description', 'Es ist ein unerwarteter Fehler aufgetreten. Wir arbeiten bereits an einer Lösung.')
@section('robots', 'noindex, nofollow')

@section('content')
    <section class="section section--large">
        <div class="container container--narrow">
            <div class="error-page">
                <div class="error-page__content">
                    <p class="error-page__code" aria-hidden="true">500</p>
                    <h1 class="error-page__title">Serverfehler</h1>
                    <p class="error-page__text">
                        Es ist ein unerwarteter Fehler aufgetreten. Wir arbeiten bereits an einer Lösung.<br>
                        Bitte versuche es in wenigen Minuten erneut.
                    </p>
                    <div class="error-page__actions">
                        <a href="{{ route('home') }}" class="btn btn--primary">
                            Zur Startseite
                        </a>
                        <a href="mailto:hallo@mens-circle.de" class="btn btn--secondary">
                            Kontakt aufnehmen
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
