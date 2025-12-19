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
                    <div class="event__card">
                        <h3>Termine & Details</h3>

                        <div class="event__meta">
                            <div class="event__meta-item">
                                <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                                <span>{{ $event->event_date->format('d.m.Y') }}</span>
                            </div>

                            <div class="event__meta-item">
                                <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                </svg>
                                <span>{{ $event->start_time->format('H:i') }} – {{ $event->end_time->format('H:i') }} Uhr</span>
                            </div>

                            <div class="event__meta-item">
                                <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                                <span>{{ $event->location }}</span>
                            </div>

                            <div class="event__meta-item">
                                <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                                <span>Max. {{ $event->max_participants }} Teilnehmer</span>
                            </div>

                            <div class="event__meta-item">
                                <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="1" x2="12" y2="23"></line>
                                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                </svg>
                                <span>{{ $event->cost_basis }}</span>
                            </div>
                        </div>

                        <button id="addToCalendar" class="btn btn--ghost btn--block">
                            <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                            Zu Kalender hinzufügen
                        </button>
                    </div>

                    <!-- Registration Form -->
                    <div class="event__card" id="anmeldung">
                        <h3>Anmeldung</h3>
                        <form id="registrationForm" class="form">
                            <input type="hidden" name="event_id" value="{{ $event->id }}">

                            <div class="form__group">
                                <label for="firstName">Vorname *</label>
                                <input type="text" id="firstName" name="first_name" required>
                            </div>

                            <div class="form__group">
                                <label for="lastName">Nachname *</label>
                                <input type="text" id="lastName" name="last_name" required>
                            </div>

                            <div class="form__group">
                                <label for="email">E-Mail *</label>
                                <input type="email" id="email" name="email" required>
                            </div>

                            <div class="form__group form__group--checkbox">
                                <label>
                                    <input type="checkbox" name="privacy" required>
                                    <span>Ich akzeptiere die <a href="{{ route('datenschutz') }}">Datenschutzerklärung</a> *</span>
                                </label>
                            </div>

                            <div id="registrationMessage"></div>

                            <button type="submit" class="btn btn--primary btn--block">
                                Jetzt anmelden
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
