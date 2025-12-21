@extends('layouts.app')

@section('title', 'Serverfehler – Männerkreis Straubing')
@section('robots', 'noindex, nofollow')

@section('content')
    <section class="section section--large">
        <div class="container container--narrow" style="text-align: center; padding: 4rem 0;">
            <div style="max-width: 600px; margin: 0 auto;">
                <h1 style="font-size: 6rem; font-weight: 600; color: var(--color-earth-deep, #3d2817); margin-bottom: 1rem; line-height: 1;">
                    500
                </h1>
                <h2 style="font-size: 2rem; font-weight: 500; margin-bottom: 1.5rem; color: var(--color-earth-deep, #3d2817);">
                    Serverfehler
                </h2>
                <p style="font-size: 1.125rem; color: #6b7280; margin-bottom: 2rem; line-height: 1.6;">
                    Es ist ein unerwarteter Fehler aufgetreten. Wir arbeiten bereits an einer Lösung.<br>
                    Bitte versuche es in wenigen Minuten erneut.
                </p>
                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                    <a href="{{ route('home') }}" class="btn btn--primary">
                        Zur Startseite
                    </a>
                    <a href="mailto:hallo@mens-circle.de" class="btn btn--secondary">
                        Kontakt aufnehmen
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
