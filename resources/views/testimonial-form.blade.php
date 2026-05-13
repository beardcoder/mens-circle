@extends ('layouts.app')

@section ('title', 'Teile deine Erfahrung – Männerkreis Niederbayern/ Straubing')
@section ('meta_description', 'Teile deine Erfahrung mit dem Männerkreis Niederbayern/ Straubing. Hilf anderen Männern zu verstehen, was der Kreis bedeuten kann.')
@section ('og_title', 'Teile deine Erfahrung – Männerkreis Niederbayern/ Straubing')

@php
    use App\Seo\Data\BreadcrumbItem;
    use App\Seo\Schemas\BreadcrumbSchema;
    use App\Seo\Schemas\WebPageSchema;
@endphp

@push ('structured_data')
  {!! (new BreadcrumbSchema([
        new BreadcrumbItem('Startseite', route('home')),
        new BreadcrumbItem('Teile deine Erfahrung', route('testimonial.form')),
    ]))->toScript() !!}
  {!! (new WebPageSchema(
        title: 'Teile deine Erfahrung',
        description: 'Teile deine Erfahrung mit dem Männerkreis Niederbayern/ Straubing. Hilf anderen Männern zu verstehen, was der Kreis bedeuten kann.',
    ))->toScript() !!}
@endpush

@section ('content')
  <!-- Testimonial Form Hero -->
  <section class="hero testimonial-form-hero">
    <div class="hero__bg"></div>
    <div class="container">
      <div class="hero__content">
        <p class="hero__label">Community Stimmen</p>
        <h1 class="hero__title">
          Teile deine <span class="highlight">Erfahrung</span>
        </h1>
        <p class="hero__subtitle">Deine Geschichte kann anderen Männern Mut machen, den ersten Schritt zu wagen.</p>
      </div>
    </div>
  </section>
  <!-- Testimonial Form Section -->
  <section class="section testimonial-form-section">
    <div class="container">
      <div class="testimonial-form__wrapper">
        <div class="testimonial-form__intro">
          <h2>Deine Stimme zählt</h2>
          <p>Der Männerkreis lebt von authentischen Begegnungen. Wenn du Teil unserer Community bist und deine Erfahrung teilen möchtest, würden wir uns freuen, von dir zu hören.</p>
          <p>Dein Testimonial wird nach Prüfung auf unserer Website veröffentlicht und kann anderen Männern helfen zu verstehen, was der Kreis bedeuten kann.</p>
        </div>

        <form
          id="testimonialForm"
          class="testimonial-form"
          x-data="testimonialForm"
          data-submit-url="{{ route('testimonial.submit') }}"
        >
          @csrf

          <div class="form-field form-field--spaced">
            <label for="quote" class="form-label form-label--plain">
              Deine Erfahrung <span class="form-required">*</span>
            </label>
            <textarea
              id="quote"
              name="quote"
              class="form-control form-control--light"
              rows="6"
              placeholder='z.B. "Hier kann ich endlich ich selbst sein, ohne Maske und ohne Leistungsdruck..."'
              required
              minlength="10"
              maxlength="1000"
            ></textarea>
            <span class="form-hint"
              >Mindestens 10 Zeichen, maximal 1000 Zeichen</span
            >
            <span class="testimonial-form__counter">
              <span id="charCount" x-text="charCount">0</span>/1000
            </span>
          </div>

          <div class="form-field form-field--spaced">
            <label for="author_name" class="form-label form-label--plain">
              Dein Name <span class="form-optional">(optional)</span>
            </label>
            <input
              type="text"
              id="author_name"
              name="author_name"
              class="form-control form-control--light"
              placeholder="z.B. Michael oder anonym lassen"
              maxlength="255"
            />
            <span class="form-hint">
              Leer lassen für ein anonymes Testimonial
            </span>
          </div>

          <div class="form-field form-field--spaced">
            <label for="role" class="form-label form-label--plain">
              Rolle/Beschreibung <span class="form-optional">(optional)</span>
            </label>
            <input
              type="text"
              id="role"
              name="role"
              class="form-control form-control--light"
              placeholder="z.B. Teilnehmer seit 2023"
              maxlength="255"
            />
          </div>

          <div class="form-field form-field--spaced">
            <label for="email" class="form-label form-label--plain">
              E-Mail-Adresse <span class="form-required">*</span>
            </label>
            <input
              type="email"
              id="email"
              name="email"
              class="form-control form-control--light"
              placeholder="deine@email.de"
              required
              maxlength="255"
            />
            <span class="form-hint">
              Wird nicht veröffentlicht. Nur für Rückfragen.
            </span>
          </div>

          <div class="form-field form-field--checkbox">
            <label class="form-checkbox-label">
              <input
                type="checkbox"
                name="privacy"
                class="form-checkbox-control"
                required
              />
              <span class="form-checkbox-text">
                Ich habe die
                <a href="/datenschutz" target="_blank" class="link"
                  >Datenschutzerklärung</a
                >
                zur Kenntnis genommen und bin damit einverstanden, dass meine
                Daten zum Zwecke der Veröffentlichung gespeichert werden.
                <span class="form-required">*</span>
              </span>
            </label>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn btn--primary">
              Erfahrung teilen
            </button>
          </div>

          <p class="testimonial-form__note">
            <small>
              Alle Felder mit <span class="form-required">*</span> sind
              Pflichtfelder.<br />
              Dein Testimonial wird nach Prüfung durch uns veröffentlicht.
            </small>
          </p>
        </form>
      </div>
    </div>
  </section>
@endsection
