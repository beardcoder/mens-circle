@extends('layouts.app')

@section('title', 'Nächster Termin – Männerkreis Straubing')
@section('meta_description', 'Melde dich jetzt für das nächste Treffen des Männerkreis Straubing an: ' . $event->title . ' am ' . $event->event_date->format('d.m.Y'))
@section('og_type', 'event')
@section('og_title', $event->title . ' – Männerkreis Straubing')

@push('structured_data')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "Event",
    "name": "{{ $event->title }}",
    "description": "{{ strip_tags($event->description) }}",
    "startDate": "{{ $event->event_date->format('Y-m-d') }}T{{ $event->start_time->format('H:i') }}",
    "endDate": "{{ $event->event_date->format('Y-m-d') }}T{{ $event->end_time->format('H:i') }}",
    "eventStatus": "https://schema.org/EventScheduled",
    "eventAttendanceMode": "https://schema.org/OfflineEventAttendanceMode",
    "location": {
        "@@type": "Place",
        "name": "{{ $event->location }}",
        "address": {
            "@@type": "PostalAddress",
            "addressLocality": "{{ $event->location }}",
            "addressRegion": "Bayern",
            "addressCountry": "DE"
        }
    },
    "organizer": {
        "@@type": "Organization",
        "name": "Männerkreis Straubing",
        "url": "{{ url('/') }}",
        "email": "hallo@mens-circle.de"
    },
    "offers": {
        "@@type": "Offer",
        "url": "{{ route('event.show') }}",
        "price": "0",
        "priceCurrency": "EUR",
        "availability": "{{ $event->isFull() ? 'https://schema.org/SoldOut' : 'https://schema.org/InStock' }}",
        "validFrom": "{{ now()->format('Y-m-d') }}"
    },
    "maximumAttendeeCapacity": {{ $event->max_participants }},
    "remainingAttendeeCapacity": {{ $event->availableSpots() }}
}
</script>
@endpush

@section('content')
    <!-- Event Hero -->
    <section class="hero event-hero">
        <div class="hero__bg"></div>
        <div class="hero__circles" aria-hidden="true">
            <div class="hero__circle hero__circle--1"></div>
            <div class="hero__circle hero__circle--2"></div>
            <div class="hero__circle hero__circle--3"></div>
        </div>

        <div class="container">
            <div class="hero__content">
                <p class="hero__label fade-in">Nächstes Treffen</p>
                <h1 class="hero__title fade-in fade-in-delay-1">
                    <span class="hero__title-line">{{ $event->title }}</span>
                </h1>
            </div>
        </div>
    </section>

    <!-- Event Details -->
    <section class="section event-section">
        <div class="container">
            <div class="event__layout">
                <div class="event__main fade-in">
                    <h2>Über dieses Treffen</h2>
                    {!! nl2br(e($event->description)) !!}
                </div>

                <div class="event__sidebar fade-in fade-in-delay-1">
                    <div class="event__card event__details-card fade-in">
                        <h3 class="event__card-title">Termine & Details</h3>

                        <div class="event__info-item">
                            <div class="event__info-icon">
                                <svg viewBox="0 0 24 24">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                            </div>
                            <div class="event__info-content">
                                <h4>Datum</h4>
                                <p>{{ $event->event_date->translatedFormat('l, d. F Y') }}</p>
                            </div>
                        </div>

                        <div class="event__info-item">
                            <div class="event__info-icon">
                                <svg viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12,6 12,12 16,14"></polyline>
                                </svg>
                            </div>
                            <div class="event__info-content">
                                <h4>Uhrzeit</h4>
                                <p>{{ $event->start_time->format('H:i') }} – {{ $event->end_time->format('H:i') }} Uhr</p>
                            </div>
                        </div>

                        <div class="event__info-item">
                            <div class="event__info-icon">
                                <svg viewBox="0 0 24 24">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                            </div>
                            <div class="event__info-content">
                                <h4>Ort</h4>
                                <p>{{ $event->location }}<br><small>Genaue Adresse nach Anmeldung</small></p>
                            </div>
                        </div>

                        <div class="event__info-item">
                            <div class="event__info-icon">
                                <svg viewBox="0 0 24 24">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                            </div>
                            <div class="event__info-content">
                                <h4>Teilnehmer</h4>
                                <p>Max. {{ $event->max_participants }} Männer</p>
                            </div>
                        </div>

                        <div class="event__info-item">
                            <div class="event__info-icon">
                                <svg viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="8" x2="12" y2="12"></line>
                                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                </svg>
                            </div>
                            <div class="event__info-content">
                                <h4>Beitrag</h4>
                                <p>{{ $event->cost_basis }}</p>
                            </div>
                        </div>

                        <button type="button" class="btn event__calendar-btn" id="addToCalendar">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                                <line x1="12" y1="14" x2="12" y2="18"></line>
                                <line x1="10" y1="16" x2="14" y2="16"></line>
                            </svg>
                            In Kalender speichern
                        </button>
                    </div>

                    <!-- Registration Form -->
                    <div class="event__card event__form-card" id="anmeldung">
                        <h3 class="event__form-title">Anmeldung</h3>
                        <p class="event__form-intro">Sichere dir deinen Platz. Du erhältst eine Bestätigung per E-Mail.</p>

                        <form id="registrationForm">
                            <input type="hidden" name="event_id" value="{{ $event->id }}">

                            <div class="form-group">
                                <label for="firstName">Vorname</label>
                                <input type="text" id="firstName" name="first_name" placeholder="Dein Vorname" required>
                            </div>

                            <div class="form-group">
                                <label for="lastName">Nachname</label>
                                <input type="text" id="lastName" name="last_name" placeholder="Dein Nachname" required>
                            </div>

                            <div class="form-group">
                                <label for="email">E-Mail</label>
                                <input type="email" id="email" name="email" placeholder="deine@email.de" required>
                            </div>

                            <label class="form-checkbox">
                                <input type="checkbox" name="privacy" required>
                                <span>Ich habe die <a href="{{ route('datenschutz') }}" target="_blank">Datenschutzerklärung</a> gelesen und stimme der Verarbeitung meiner Daten zu.</span>
                            </label>

                            <button type="submit" class="btn btn--primary event__submit-btn">
                                Verbindlich anmelden
                            </button>

                            <div id="registrationMessage"></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
