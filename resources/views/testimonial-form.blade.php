@extends('layouts.app')

@section('title', 'Teile deine Erfahrung – Männerkreis Niederbayern/ Straubing')
@section('meta_description', 'Teile deine Erfahrung mit dem Männerkreis Niederbayern/ Straubing. Hilf anderen Männern zu verstehen, was der Kreis bedeuten kann.')
@section('og_title', 'Teile deine Erfahrung – Männerkreis Niederbayern/ Straubing')

<x-seo.breadcrumb-schema :items="[
    ['name' => 'Startseite', 'url' => route('home')],
    ['name' => 'Teile deine Erfahrung', 'url' => route('testimonial.form')],
]" />

@push('structured_data')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "WebPage",
    "name": "Teile deine Erfahrung",
    "description": "Teile deine Erfahrung mit dem Männerkreis Niederbayern/ Straubing",
    "url": "{{ route('testimonial.form') }}",
    "inLanguage": "de-DE",
    "isPartOf": {
        "@@type": "WebSite",
        "@@id": "{{ url('/') }}#website"
    }
}
</script>
@endpush

@section('content')
    <!-- Testimonial Form Hero -->
    <section class="hero testimonial-form-hero">
        <div class="hero__bg"></div>
        <div class="container">
            <div class="hero__content">
                <p class="hero__label">Community Stimmen</p>
                <h1 class="hero__title">
                    Teile deine <span class="highlight">Erfahrung</span>
                </h1>
                <p class="hero__subtitle">
                    Deine Geschichte kann anderen Männern Mut machen, den ersten Schritt zu wagen.
                </p>
            </div>
        </div>
    </section>

    <!-- Testimonial Form Section -->
    <section class="section testimonial-form-section">
        <div class="container">
            <div class="testimonial-form__wrapper">
                <div class="testimonial-form__intro fade-in">
                    <h2>Deine Stimme zählt</h2>
                    <p>
                        Der Männerkreis lebt von authentischen Begegnungen. Wenn du Teil unserer Community bist und deine Erfahrung teilen möchtest, würden wir uns freuen, von dir zu hören.
                    </p>
                    <p>
                        Dein Testimonial wird nach Prüfung auf unserer Website veröffentlicht und kann anderen Männern helfen zu verstehen, was der Kreis bedeuten kann.
                    </p>
                </div>

                <form id="testimonialForm" class="testimonial-form fade-in" data-submit-url="{{ route('testimonial.submit') }}">
                    @csrf

                    <div class="form__group">
                        <label for="quote" class="form__label">
                            Deine Erfahrung <span class="required">*</span>
                        </label>
                        <textarea
                            id="quote"
                            name="quote"
                            class="form__textarea"
                            rows="6"
                            placeholder="z.B. &quot;Hier kann ich endlich ich selbst sein, ohne Maske und ohne Leistungsdruck...&quot;"
                            required
                            minlength="10"
                            maxlength="1000"
                        ></textarea>
                        <span class="form__hint">Mindestens 10 Zeichen, maximal 1000 Zeichen</span>
                        <span class="form__counter">
                            <span id="charCount">0</span>/1000
                        </span>
                    </div>

                    <div class="form__group">
                        <label for="author_name" class="form__label">
                            Dein Name <span class="optional">(optional)</span>
                        </label>
                        <input
                            type="text"
                            id="author_name"
                            name="author_name"
                            class="form__input"
                            placeholder="z.B. Michael oder anonym lassen"
                            maxlength="255"
                        />
                        <span class="form__hint">
                            Leer lassen für ein anonymes Testimonial
                        </span>
                    </div>

                    <div class="form__group">
                        <label for="role" class="form__label">
                            Rolle/Beschreibung <span class="optional">(optional)</span>
                        </label>
                        <input
                            type="text"
                            id="role"
                            name="role"
                            class="form__input"
                            placeholder="z.B. Teilnehmer seit 2023"
                            maxlength="255"
                        />
                    </div>

                    <div class="form__group">
                        <label for="email" class="form__label">
                            E-Mail-Adresse <span class="required">*</span>
                        </label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form__input"
                            placeholder="deine@email.de"
                            required
                            maxlength="255"
                        />
                        <span class="form__hint">
                            Wird nicht veröffentlicht. Nur für Rückfragen.
                        </span>
                    </div>

                    <div class="form__group form__group--checkbox">
                        <label class="form__checkbox-label">
                            <input
                                type="checkbox"
                                name="privacy"
                                class="form__checkbox"
                                required
                            />
                            <span class="form__checkbox-text">
                                Ich habe die <a href="/datenschutz" target="_blank" class="link">Datenschutzerklärung</a> zur Kenntnis genommen und bin damit einverstanden, dass meine Daten zum Zwecke der Veröffentlichung gespeichert werden. <span class="required">*</span>
                            </span>
                        </label>
                    </div>

                    <div id="formMessage" class="form__message" style="display: none;"></div>

                    <div class="form__actions">
                        <button type="submit" class="btn btn--primary" id="submitBtn">
                            <span class="btn__text">Erfahrung teilen</span>
                            <span class="btn__loader" style="display: none;">
                                <svg class="spinner" width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <circle class="spinner__path" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" cx="10" cy="10" r="8"/>
                                </svg>
                            </span>
                        </button>
                    </div>

                    <p class="form__note">
                        <small>
                            Alle Felder mit <span class="required">*</span> sind Pflichtfelder.<br>
                            Dein Testimonial wird nach Prüfung durch uns veröffentlicht.
                        </small>
                    </p>
                </form>
            </div>
        </div>
    </section>
@endsection
