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
            <div class="hero__circle hero__circle--4"></div>
        </div>

        <div class="container">
            <div class="hero__content">
                <p class="hero__label fade-in">Nächstes Treffen</p>
                <h1 class="hero__title fade-in fade-in-delay-1">
                    <span class="hero__title-line">{{ $event->title }}</span>
                </h1>
                <div class="hero__bottom fade-in fade-in-delay-2">
                    <p class="hero__description">
                        {{ $event->event_date->translatedFormat('l') }}, {{ $event->event_date->format('d.m.Y') }} · {{ $event->start_time->format('H:i') }} Uhr · {{ $event->location }}
                    </p>
                    <div class="hero__cta">
                        <a href="#anmeldung" class="btn btn--primary btn--large">Jetzt anmelden</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Event Intro with Quote -->
    <section class="event-intro-section">
        <div class="event-intro__layout">
            <div class="event-intro__left">
                <p class="event-intro__eyebrow fade-in">Über das Treffen</p>
                <h2 class="event-intro__title fade-in fade-in-delay-1">
                    Ein Raum für <span class="text-italic">echte</span> Begegnung
                </h2>
                <div class="event-intro__text fade-in fade-in-delay-2">
                    {!! nl2br(e($event->description)) !!}
                </div>
            </div>
            <div class="event-intro__right">
                <div class="event-intro__quote-area">
                    <div class="event-intro__circles"></div>
                    <p class="event-intro__quote">
                        »Gemeinsam <span class="text-italic">wachsen</span>,<br>
                        einander <span class="text-italic">stärken</span>«
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Event Details with large typography -->
    <section class="section section--large event-details-section">
        <div class="container">
            <div class="event-details__header fade-in">
                <p class="event-details__eyebrow">Auf einen Blick</p>
                <h2 class="event-details__title">Termin & <span class="text-italic">Details</span></h2>
            </div>

            <div class="event-details__grid stagger-children">
                <div class="event-detail-card">
                    <span class="event-detail-card__number">01</span>
                    <div class="event-detail-card__content">
                        <h3>Datum</h3>
                        <p class="event-detail-card__value">{{ $event->event_date->translatedFormat('l') }}</p>
                        <p class="event-detail-card__sub">{{ $event->event_date->format('d. F Y') }}</p>
                    </div>
                </div>

                <div class="event-detail-card">
                    <span class="event-detail-card__number">02</span>
                    <div class="event-detail-card__content">
                        <h3>Uhrzeit</h3>
                        <p class="event-detail-card__value">{{ $event->start_time->format('H:i') }} Uhr</p>
                        <p class="event-detail-card__sub">bis {{ $event->end_time->format('H:i') }} Uhr</p>
                    </div>
                </div>

                <div class="event-detail-card">
                    <span class="event-detail-card__number">03</span>
                    <div class="event-detail-card__content">
                        <h3>Ort</h3>
                        <p class="event-detail-card__value">{{ $event->location }}</p>
                        <p class="event-detail-card__sub">Genaue Adresse nach Anmeldung</p>
                    </div>
                </div>

                <div class="event-detail-card">
                    <span class="event-detail-card__number">04</span>
                    <div class="event-detail-card__content">
                        <h3>Teilnehmer</h3>
                        <p class="event-detail-card__value">Max. {{ $event->max_participants }}</p>
                        <p class="event-detail-card__sub">{{ $event->cost_basis }}</p>
                    </div>
                </div>
            </div>

            <div class="event-details__calendar fade-in">
                <button type="button" class="btn btn--secondary" id="addToCalendar">
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
        </div>
    </section>

    <!-- Registration CTA Section -->
    <section class="section section--large event-cta-section" id="anmeldung">
        <div class="container">
            <div class="event-cta__layout">
                <div class="event-cta__content fade-in">
                    <p class="event-cta__eyebrow">Sei dabei</p>
                    <h2 class="event-cta__title">Sichere dir <span class="text-italic">deinen</span> Platz</h2>
                    <p class="event-cta__text">Du erhältst eine Bestätigung per E-Mail mit allen weiteren Details.</p>
                </div>

                <div class="event-cta__form fade-in fade-in-delay-1">
                    <form id="registrationForm" class="event-registration-form">
                        <input type="hidden" name="event_id" value="{{ $event->id }}">

                        <div class="form-row">
                            <div class="form-group">
                                <label for="firstName">Vorname</label>
                                <input type="text" id="firstName" name="first_name" placeholder="Dein Vorname" required>
                            </div>

                            <div class="form-group">
                                <label for="lastName">Nachname</label>
                                <input type="text" id="lastName" name="last_name" placeholder="Dein Nachname" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email">E-Mail</label>
                            <input type="email" id="email" name="email" placeholder="deine@email.de" required>
                        </div>

                        <label class="form-checkbox">
                            <input type="checkbox" name="privacy" required>
                            <span>Ich habe die <a href="{{ route('page.show', 'datenschutz') }}" target="_blank">Datenschutzerklärung</a> gelesen und stimme der Verarbeitung meiner Daten zu.</span>
                        </label>

                        <button type="submit" class="btn btn--primary btn--large event__submit-btn">
                            Verbindlich anmelden
                        </button>

                        <div id="registrationMessage"></div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    // Pass event data to JavaScript for calendar integration
    window.eventData = {
        title: '{{ $event->title }}',
        description: '{{ strip_tags($event->description) }}',
        location: '{{ $event->location }}',
        startDate: '{{ $event->event_date->format('Y-m-d') }}',
        startTime: '{{ $event->start_time->format('H:i') }}',
        endDate: '{{ $event->event_date->format('Y-m-d') }}',
        endTime: '{{ $event->end_time->format('H:i') }}'
    };
</script>
@endpush
