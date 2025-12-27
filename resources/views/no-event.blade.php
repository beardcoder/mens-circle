@extends('layouts.app')

@section('title', 'Aktuell kein Termin – Männerkreis Straubing')
@section('meta_description', 'Derzeit ist kein Männerkreis-Treffen geplant. Melde dich für unseren Newsletter an, um über kommende Termine informiert zu werden.')

@section('content')
    <!-- Hero Section -->
    <section class="hero no-event-hero">
        <div class="hero__circles" aria-hidden="true">
            <div class="hero__circle hero__circle--1"></div>
            <div class="hero__circle hero__circle--2"></div>
            <div class="hero__circle hero__circle--3"></div>
            <div class="hero__circle hero__circle--4"></div>
        </div>

        <div class="container">
            <div class="hero__content">
                <p class="hero__label fade-in">Männerkreis Straubing</p>
                <h1 class="hero__title fade-in fade-in-delay-1">
                    <span class="hero__title-line">Aktuell ist kein</span>
                    <span class="hero__title-line"><span class="text-italic">Termin</span> geplant</span>
                </h1>
                <div class="hero__bottom fade-in fade-in-delay-2">
                    <p class="hero__description">
                        Wir planen gerade unser nächstes Treffen. Melde dich für unseren Newsletter an
                        oder tritt unserer WhatsApp-Community bei, um als Erster zu erfahren, wann es weitergeht.
                    </p>
                    <div class="hero__cta">
                        <a href="#newsletter" class="btn btn--primary btn--large">Zum Newsletter</a>
                        <div class="hero__scroll">
                            <span>Mehr erfahren</span>
                            <div class="hero__scroll-line"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Info Section -->
    <section class="section no-event-info-section">
        <div class="container">
            <div class="no-event-info__layout fade-in">
                <div class="no-event-info__content">
                    <p class="eyebrow">Was ist der Männerkreis?</p>
                    <h2 class="section-title no-event-info__title">
                        Ein Raum für <span class="text-italic">echte Begegnung</span>
                    </h2>
                    <p class="no-event-info__text">
                        Der Männerkreis Straubing bietet dir einen geschützten Raum, in dem du dich mit anderen Männern
                        austauschen, wachsen und echte Verbindungen aufbauen kannst. Unsere Treffen finden regelmäßig statt –
                        sobald der nächste Termin feststeht, informieren wir dich.
                    </p>
                </div>
                <div class="no-event-info__visual fade-in fade-in-delay-1">
                    <div class="no-event-info__quote-area">
                        <div class="event-about__circles" aria-hidden="true">
                            <div class="event-about__circle event-about__circle--1"></div>
                            <div class="event-about__circle event-about__circle--2"></div>
                            <div class="event-about__circle event-about__circle--3"></div>
                        </div>
                        <p class="event-about__quote">
                            »Bleib<br>
                            <span class="text-italic">verbunden</span>«
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="section newsletter-section" id="newsletter">
        <div class="container">
            <div class="newsletter__layout fade-in">
                <div class="newsletter__content">
                    <p class="eyebrow eyebrow--secondary">Newsletter</p>
                    <h2 class="section-title newsletter__title">Bleib <span class="text-italic">informiert</span></h2>
                    <p class="newsletter__text">
                        Erhalte als Erster Bescheid, wenn unser nächstes Treffen stattfindet.
                        Kein Spam, nur relevante Informationen zum Männerkreis.
                    </p>
                </div>

                <div class="newsletter__form-wrapper">
                    <form id="newsletterForm" class="newsletter__form">
                        <input
                            type="email"
                            name="email"
                            placeholder="Deine E-Mail-Adresse"
                            required
                            class="newsletter__input"
                            aria-label="E-Mail-Adresse"
                        >
                        <button type="submit" class="btn btn--primary">
                            Anmelden
                        </button>
                        <div id="newsletterMessage"></div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- WhatsApp Community Section -->
    <x-blocks.whatsapp-community />

    <!-- Back to Home CTA -->
    <section class="section no-event-cta-section">
        <div class="container">
            <div class="no-event-cta__content fade-in">
                <p class="eyebrow">Mehr erfahren</p>
                <h2 class="section-title no-event-cta__title">
                    Entdecke den <span class="text-italic">Männerkreis</span>
                </h2>
                <p class="no-event-cta__text">
                    Erfahre mehr über uns, unsere Werte und was dich bei einem Treffen erwartet.
                </p>
                <a href="{{ route('home') }}" class="btn btn--primary btn--large">Zur Startseite</a>
            </div>
        </div>
    </section>
@endsection
