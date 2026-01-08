@extends('layouts.app')

@section('title', 'Newsletter abgemeldet – Männerkreis Niederbayern/ Straubing')
@section('meta_description', 'Du hast dich erfolgreich von unserem Newsletter abgemeldet.')
@section('robots', 'noindex, nofollow')

@section('content')
    <section class="section section--large">
        <div class="container container--narrow">
            <div class="error-page">
                <div class="error-page__content">
                    <h1 class="error-page__title">Newsletter abgemeldet</h1>
                    <p class="error-page__text">{{ $message }}</p>
                    <div class="error-page__actions">
                        <a href="{{ route('home') }}" class="btn btn--primary">Zurück zur Startseite</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
