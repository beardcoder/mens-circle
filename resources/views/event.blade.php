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
        <div class="hero__bg">
            @if($event->image)
                <x-modern-image
                    :src="$event->image"
                    alt="{{ $event->title }}"
                    class="hero__bg-image"
                    loading="eager"
                    fetchpriority="high"
                />
            @endif
        </div>
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

    <!-- Registration Section - Prominent Placement -->
    <section class="event-register-section" id="anmeldung">
        <div class="event-register__layout">
            <div class="event-register__content fade-in">
                <div class="event-register__circles" aria-hidden="true">
                    <div class="event-register__circle event-register__circle--1"></div>
                    <div class="event-register__circle event-register__circle--2"></div>
                </div>
                <p class="event-register__eyebrow">Sei dabei</p>
                <h2 class="event-register__title">
                    Sichere dir <br><span class="text-italic">deinen Platz</span>
                </h2>
                <p class="event-register__spots">
                    @if($event->isFull())
                        <span class="event-register__spots-full">Ausgebucht</span>
                    @else
                        <span class="event-register__spots-available">{{ $event->availableSpots() }}</span>
                        <span>von {{ $event->max_participants }} Plätzen frei</span>
                    @endif
                </p>
            </div>

            <div class="event-register__form-wrap fade-in fade-in-delay-1">
                <form id="registrationForm" class="event-register__form" autocomplete="on">
                    <input type="hidden" name="event_id" value="{{ $event->id }}">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="firstName">Vorname</label>
                            <input type="text" id="firstName" name="first_name" placeholder="Dein Vorname" required autocomplete="given-name">
                        </div>

                        <div class="form-group">
                            <label for="lastName">Nachname</label>
                            <input type="text" id="lastName" name="last_name" placeholder="Dein Nachname" required autocomplete="family-name">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">E-Mail</label>
                        <input type="email" id="email" name="email" placeholder="deine@email.de" required autocomplete="email" inputmode="email">
                    </div>

                    <div class="form-group">
                        <label for="phone">Handynummer <span class="form-label-optional">(optional)</span></label>
                        <input type="tel" id="phone" name="phone_number" placeholder="+49 170 1234567" autocomplete="tel" inputmode="tel">
                        <span class="form-helper">Für Erinnerungen per SMS am Veranstaltungstag</span>
                    </div>

                    <label class="form-checkbox">
                        <input type="checkbox" name="privacy" required>
                        <span>Ich habe die <a href="{{ route('page.show', 'datenschutz') }}" target="_blank">Datenschutzerklärung</a> gelesen und stimme der Verarbeitung meiner Daten zu.</span>
                    </label>

                    <button type="submit" class="btn btn--primary btn--large event-register__submit" {{ $event->isFull() ? 'disabled' : '' }}>
                        {{ $event->isFull() ? 'Ausgebucht' : 'Verbindlich anmelden' }}
                    </button>

                    <div id="registrationMessage"></div>
                </form>
            </div>
        </div>
    </section>

    <!-- Event Info Section with Large Typography -->
    <section class="event-info-section">
        <div class="event-info__bg-text" aria-hidden="true">TERMIN</div>
        <div class="container">
            <div class="event-info__grid stagger-children">
                <div class="event-info__card event-info__card--date">
                    <div class="event-info__card-circle" aria-hidden="true"></div>
                    <span class="event-info__card-number">01</span>
                    <div class="event-info__card-content">
                        <h3>Datum</h3>
                        <p class="event-info__card-value">{{ $event->event_date->translatedFormat('l') }}</p>
                        <p class="event-info__card-sub">{{ $event->event_date->format('d. F Y') }}</p>
                    </div>
                </div>

                <div class="event-info__card event-info__card--time">
                    <div class="event-info__card-circle" aria-hidden="true"></div>
                    <span class="event-info__card-number">02</span>
                    <div class="event-info__card-content">
                        <h3>Uhrzeit</h3>
                        <p class="event-info__card-value">{{ $event->start_time->format('H:i') }} Uhr</p>
                        <p class="event-info__card-sub">bis {{ $event->end_time->format('H:i') }} Uhr</p>
                    </div>
                </div>

                <div class="event-info__card event-info__card--location">
                    <div class="event-info__card-circle" aria-hidden="true"></div>
                    <span class="event-info__card-number">03</span>
                    <div class="event-info__card-content">
                        <h3>Ort</h3>
                        <p class="event-info__card-value">{{ $event->location }}</p>
                        <p class="event-info__card-sub">Genaue Adresse nach Anmeldung</p>
                    </div>
                </div>

                <div class="event-info__card event-info__card--participants">
                    <div class="event-info__card-circle" aria-hidden="true"></div>
                    <span class="event-info__card-number">04</span>
                    <div class="event-info__card-content">
                        <h3>Teilnehmer</h3>
                        <p class="event-info__card-value">Max. {{ $event->max_participants }}</p>
                        <p class="event-info__card-sub">{{ $event->cost_basis }}</p>
                    </div>
                </div>
            </div>

            <div class="event-info__calendar fade-in">
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

    <!-- Event Description Section -->
    <section class="event-about-section">
        <div class="event-about__layout">
            <div class="event-about__content fade-in">
                <p class="event-about__eyebrow">Über das Treffen</p>
                <h2 class="event-about__title">
                    Ein Raum für <br><span class="text-italic">echte Begegnung</span>
                </h2>
                <div class="event-about__text">
                    {!! nl2br(e($event->description)) !!}
                </div>
            </div>
            <div class="event-about__visual fade-in fade-in-delay-1">
                <div class="event-about__quote-area">
                    <div class="event-about__circles" aria-hidden="true">
                        <div class="event-about__circle event-about__circle--1"></div>
                        <div class="event-about__circle event-about__circle--2"></div>
                        <div class="event-about__circle event-about__circle--3"></div>
                    </div>
                    <p class="event-about__quote">
                        »Gemeinsam<br>
                        <span class="text-italic">wachsen</span>,<br>
                        einander<br>
                        <span class="text-italic">stärken</span>«
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Final CTA Section -->
    <section class="event-cta-section">
        <div class="event-cta__circles" aria-hidden="true">
            <div class="event-cta__circle event-cta__circle--1"></div>
            <div class="event-cta__circle event-cta__circle--2"></div>
        </div>
        <div class="container">
            <div class="event-cta__content fade-in">
                <p class="event-cta__eyebrow">Bereit?</p>
                <h2 class="event-cta__title">
                    Melde dich <span class="text-italic">jetzt</span> an
                </h2>
                <a href="#anmeldung" class="btn btn--primary btn--large">Zur Anmeldung</a>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    // Pass event data to JavaScript for calendar integration
    window.eventData = {
        title: @json($event->title),
        description: @json(strip_tags($event->description)),
        location: @json($event->location),
        startDate: @json($event->event_date->format('Y-m-d')),
        startTime: @json($event->start_time->format('H:i')),
        endDate: @json($event->event_date->format('Y-m-d')),
        endTime: @json($event->end_time->format('H:i'))
    };
</script>
@endpush
