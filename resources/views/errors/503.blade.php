@extends('layouts.app')

@section('title', 'Wartungsarbeiten – Männerkreis Niederbayern/ Straubing')
@section('meta_description', 'Wir führen gerade Wartungsarbeiten durch. Die Website ist in Kürze wieder verfügbar.')
@section('robots', 'noindex, nofollow')

@section('content')
    <section class="section section--large">
        <div class="container container--narrow">
            <div class="error-page">
                <div class="error-page__content">
                    <p class="error-page__code" aria-hidden="true">503</p>
                    <h1 class="error-page__title">Wartungsarbeiten</h1>
                    <p class="error-page__text">
                        Wir führen gerade Wartungsarbeiten durch.<br>
                        Die Website ist in Kürze wieder verfügbar.
                    </p>
                    <p class="error-page__contact">
                        Bei dringenden Fragen erreichst du uns per E-Mail:<br>
                        <a href="mailto:hallo@mens-circle.de">hallo@mens-circle.de</a>
                    </p>
                </div>
            </div>
        </div>
    </section>
@endsection
