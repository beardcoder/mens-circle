@extends('layouts.app')

@section('title', 'Newsletter abgemeldet – Männerkreis Niederbayern/ Straubing')

@section('content')
    <section class="section">
        <div class="container container--narrow text-center">
            <h1>Newsletter abgemeldet</h1>
            <p>{{ $message }}</p>
            <a href="{{ route('home') }}" class="btn btn--primary">Zurück zur Startseite</a>
        </div>
    </section>
@endsection
