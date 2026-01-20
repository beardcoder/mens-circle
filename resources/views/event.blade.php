@extends('layouts.app')

@section('title', $event->title . ' am ' . $event->event_date->format('d.m.Y') . ' – Männerkreis Niederbayern/ Straubing')
@section('meta_description', $event->isPast
    ? 'Rückblick auf das Treffen des Männerkreis Niederbayern/ Straubing: ' . $event->title . ' am ' . $event->event_date->format('d.m.Y')
    : 'Melde dich jetzt für das nächste Treffen des Männerkreis Niederbayern/ Straubing an: ' . $event->title . ' am ' . $event->event_date->format('d.m.Y'))
@section('og_type', 'event')
@section('og_title', $event->title . ' am ' . $event->event_date->format('d.m.Y') . ' – Männerkreis Niederbayern/ Straubing')
@section('og_description', 'Treffen des Männerkreis Niederbayern/ Straubing am ' . $event->event_date->format('d.m.Y') . ' in ' . $event->location)
@section('canonical', route('event.show.slug', $event->slug))

<x-seo.breadcrumb-schema :items="[
    ['name' => 'Startseite', 'url' => route('home')],
    ['name' => 'Veranstaltungen', 'url' => route('event.show')],
    ['name' => $event->title, 'url' => route('event.show.slug', $event->slug)],
]" />

@push('structured_data')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "Event",
    "name": "{{ $event->title }}",
    "description": "{{ e(strip_tags($event->description)) }}",
    "image": {
        "@@type": "ImageObject",
        "url": "{{ asset('images/logo-color.png') }}",
        "width": 512,
        "height": 512
    },
    "startDate": "{{ $event->event_date->format('Y-m-d') }}T{{ $event->start_time->format('H:i') }}:00+01:00",
    "endDate": "{{ $event->event_date->format('Y-m-d') }}T{{ $event->end_time->format('H:i') }}:00+01:00",
    "eventStatus": "{{ $event->isPast ? 'https://schema.org/EventPostponed' : 'https://schema.org/EventScheduled' }}",
    "eventAttendanceMode": "https://schema.org/OfflineEventAttendanceMode",
    "location": {
        "@@type": "Place",
        "name": "{{ $event->location }}",
        "address": {
            "@@type": "PostalAddress",
            "addressLocality": "{{ $event->city ?? 'Straubing' }}",
            "addressRegion": "Bayern",
            "addressCountry": "DE"
        }
    },
    "organizer": {
        "@@type": "Organization",
        "@@id": "{{ url('/') }}#organization",
        "name": "Männerkreis Niederbayern/ Straubing",
        "url": "{{ url('/') }}"
    },
    "performer": {
        "@@type": "Organization",
        "@@id": "{{ url('/') }}#organization"
    },
    "offers": {
        "@@type": "Offer",
        "url": "{{ route('event.show.slug', $event->slug) }}",
        "price": "0",
        "priceCurrency": "EUR",
        "availability": "{{ $event->isPast ? 'https://schema.org/SoldOut' : ($event->isFull ? 'https://schema.org/SoldOut' : 'https://schema.org/InStock') }}",
        "validFrom": "{{ now()->format('Y-m-d') }}"
    },
    "maximumAttendeeCapacity": {{ $event->max_participants }},
    "remainingAttendeeCapacity": {{ max(0, $event->availableSpots) }},
    "inLanguage": "de"
}
</script>
@endpush

@section('content')
    <!-- Event Hero -->
    <section class="hero event-hero">
        <div class="hero__bg">
            @if($eventImage)
                {{ $eventImage->img()->attributes([
                    'class' => 'hero__bg-image',
                    'loading' => 'eager',
                    'fetchpriority' => 'high',
                    'aria-hidden' => 'true',
                    'alt' => $event->title,
                ]) }}
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
                <p class="hero__label fade-in">{{ $event->isPast ? 'Vergangenes Treffen' : 'Nächstes Treffen' }}</p>
                <h1 class="hero__title fade-in fade-in-delay-1">
                    <span class="hero__title-line">{{ $event->title }}</span>
                </h1>
                <div class="hero__bottom">
                    <p class="hero__description">
                        {{ $event->event_date->translatedFormat('l') }}, {{ $event->event_date->format('d.m.Y') }} · {{ $event->start_time->format('H:i') }} Uhr · {{ $event->location }}
                    </p>
                    @unless($event->isPast)
                        <div class="hero__cta fade-in-delay-2">
                            <a href="#anmeldung" class="btn btn--primary btn--large" data-m:click="action=cta_click;element=button;target=registration;location=hero">Jetzt anmelden</a>
                        </div>
                    @endunless
                </div>
            </div>
        </div>
    </section>

    @if($event->isPast)
        <!-- Past Event Info Section -->
        <section class="event-register-section" id="anmeldung">
            <div class="event-register__layout">
                <div class="event-register__content fade-in">
                    <div class="event-register__circles" aria-hidden="true">
                        <div class="event-register__circle event-register__circle--1"></div>
                        <div class="event-register__circle event-register__circle--2"></div>
                    </div>
                    <p class="eyebrow eyebrow--secondary">Rückblick</p>
                    <h2 class="section-title section-title--lg section-title--light event-register__title">
                        Dieses Treffen <br><span class="text-italic">hat stattgefunden</span>
                    </h2>
                    <p class="event-register__spots">
                        <span>Am {{ $event->event_date->format('d.m.Y') }}</span>
                    </p>
                </div>

                <div class="event-register__form-wrap fade-in fade-in-delay-1">
                    <div class="event-register__past-info">
                        <p class="event-register__past-text">
                            Dieses Treffen liegt in der Vergangenheit. Eine Anmeldung ist nicht mehr möglich.
                        </p>
                        <p class="event-register__past-text">
                            Möchtest du beim nächsten Männerkreis dabei sein? Dann trag dich in unseren Newsletter ein, um über kommende Termine informiert zu werden.
                        </p>
                        <a href="{{ route('home') }}#newsletter" class="btn btn--primary btn--large">Zum Newsletter anmelden</a>
                    </div>
                </div>
            </div>
        </section>
    @else
        <!-- Registration Section - Prominent Placement -->
        <section class="event-register-section" id="anmeldung">
            <div class="event-register__layout">
                <div class="event-register__content fade-in">
                    <div class="event-register__circles" aria-hidden="true">
                        <div class="event-register__circle event-register__circle--1"></div>
                        <div class="event-register__circle event-register__circle--2"></div>
                    </div>
                    <p class="eyebrow eyebrow--secondary">Sei dabei</p>
                    <h2 class="section-title section-title--lg section-title--light event-register__title">
                        Sichere dir <br><span class="text-italic">deinen Platz</span>
                    </h2>
                    <p class="event-register__spots">
                        @if($event->isFull)
                            <span class="event-register__spots-full">Ausgebucht</span>
                        @else
                            <span class="event-register__spots-available">{{ $event->availableSpots }}</span>
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
                            <span>Ich habe die <a href="{{ route('page.show', 'datenschutz') }}" target="_blank" data-m:click="action=legal_click;element=link;target=datenschutz;location=registration_form">Datenschutzerklärung</a> gelesen und stimme der Verarbeitung meiner Daten zu.</span>
                        </label>

                        <button type="submit" class="btn btn--primary btn--large event-register__submit" {{ $event->isFull ? 'disabled' : '' }} data-m:click="action=form_submit;element=button;target=event_registration;location=registration_form">
                            {{ $event->isFull ? 'Ausgebucht' : 'Verbindlich anmelden' }}
                        </button>

                        <div id="registrationMessage"></div>
                    </form>
                </div>
            </div>
        </section>
    @endif

    <!-- Event Info Section with Large Typography -->
    <section class="event-info-section">
        <div class="event-info__bg-text" aria-hidden="true">TERMIN</div>
        <div class="container">
            <div class="event-info__grid stagger-children">
                <div class="event-info__card event-info__card--date">
                    <div class="event-info__card-circle" aria-hidden="true"></div>
                    <div class="event-info__card-content">
                        <h3>Datum</h3>
                        <p class="event-info__card-value">{{ $event->event_date->translatedFormat('l') }}</p>
                        <p class="event-info__card-sub">{{ $event->event_date->format('d. F Y') }}</p>
                    </div>
                </div>

                <div class="event-info__card event-info__card--time">
                    <div class="event-info__card-circle" aria-hidden="true"></div>
                    <div class="event-info__card-content">
                        <h3>Uhrzeit</h3>
                        <p class="event-info__card-value">{{ $event->start_time->format('H:i') }} Uhr</p>
                        <p class="event-info__card-sub">bis {{ $event->end_time->format('H:i') }} Uhr</p>
                    </div>
                </div>

                <div class="event-info__card event-info__card--location">
                    <div class="event-info__card-circle" aria-hidden="true"></div>
                    <div class="event-info__card-content">
                        <h3>Ort</h3>
                        <p class="event-info__card-value">{{ $event->location }}</p>
                        <p class="event-info__card-sub">Genaue Adresse nach Anmeldung</p>
                    </div>
                </div>

                <div class="event-info__card event-info__card--participants">
                    <div class="event-info__card-circle" aria-hidden="true"></div>
                    <div class="event-info__card-content">
                        <h3>Teilnehmer</h3>
                        <p class="event-info__card-value">Max. {{ $event->max_participants }}</p>
                        <p class="event-info__card-sub">{{ $event->cost_basis }}</p>
                    </div>
                </div>
            </div>

            <div class="event-info__calendar fade-in">
                <button type="button" class="btn btn--secondary" id="addToCalendar" data-m:click="action=calendar_click;element=button;target=open_modal;location=event_info">
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
                <p class="eyebrow">Über das Treffen</p>
                <h2 class="section-title section-title--lg event-about__title">
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
                @if($event->isPast)
                    <p class="eyebrow">Interesse geweckt?</p>
                    <h2 class="section-title event-cta__title">
                        Bleib <span class="text-italic">informiert</span>
                    </h2>
                    <a href="{{ route('home') }}#newsletter" class="btn btn--primary btn--large">Newsletter abonnieren</a>
                @else
                    <p class="eyebrow">Bereit?</p>
                    <h2 class="section-title event-cta__title">
                        Melde dich <span class="text-italic">jetzt</span> an
                    </h2>
                    <a href="#anmeldung" class="btn btn--primary btn--large" data-m:click="action=cta_click;element=button;target=registration;location=event_cta">Zur Anmeldung</a>
                @endif
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
