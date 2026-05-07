@extends ('layouts.app')

@section ('title', 'Aktuell kein Termin – Männerkreis Niederbayern/ Straubing')
@section ('meta_description', 'Derzeit ist kein Männerkreis-Treffen geplant. Melde dich für unseren Newsletter an, um über kommende Termine informiert zu werden.')
@section ('og_title', 'Aktuell kein Termin – Männerkreis Niederbayern/ Straubing')
@section ('og_description', 'Derzeit ist kein Männerkreis-Treffen geplant. Melde dich für unseren Newsletter an!')

@php
    use App\Seo\Data\BreadcrumbItem;
    use App\Seo\Schemas\BreadcrumbSchema;
    use App\Seo\Schemas\WebPageSchema;
@endphp

@push ('structured_data')
  {!! (new BreadcrumbSchema([
        new BreadcrumbItem('Startseite', route('home')),
        new BreadcrumbItem('Veranstaltungen', route('event.show')),
    ]))->toScript() !!}
  {!! (new WebPageSchema(
        title: 'Aktuell kein Termin',
        description: 'Derzeit ist kein Männerkreis-Treffen geplant. Melde dich für unseren Newsletter an, um über kommende Termine informiert zu werden.',
    ))->toScript() !!}
@endpush

@section ('content')
  <!-- Hero Section -->
  <section class="hero no-event-hero">
    <div class="hero__circles" aria-hidden="true">
      <div class="hero__circle hero__circle--1"></div>
      <div class="hero__circle hero__circle--2"></div>
      <div class="hero__circle hero__circle--3"></div>
      <div class="hero__circle hero__circle--4"></div>
    </div>

    <div class="container">
      <div class="hero__content" data-anim-group>
        <p
          class="story-kicker story-kicker--on-dark hero__chapter"
          data-anim="trace"
        >
          <span>Zwischenraum</span>
          <span>Der nächste Kreis</span>
        </p>

        <p class="hero__label" data-anim="rise">Männerkreis Niederbayern/ Straubing</p>
        <h1 class="hero__title" data-anim="lift">
          <span class="hero__title-line">Aktuell ist kein</span>
          <span class="hero__title-line"
            ><span class="text-italic">Termin</span> geplant</span
          >
        </h1>
        <div class="hero__bottom">
          <p class="hero__description" data-anim="rise">Wir planen gerade unser nächstes Treffen. Melde dich für unseren Newsletter an oder tritt unserer WhatsApp-Community bei, um als Erster zu erfahren, wann es weitergeht.</p>
          <div class="hero__cta" data-anim="rise">
            <a href="#newsletter" class="btn btn--primary btn--large"
              >Zum Newsletter</a
            >
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
      <div class="no-event-info__layout">
        <div class="no-event-info__content" data-anim-group>
          <p class="story-kicker" data-anim="trace">
            <span>Kapitel</span>
            <span>Verbunden bleiben</span>
          </p>

          <p class="eyebrow" data-anim="rise">Was ist der Männerkreis?</p>
          <h2 class="section-title no-event-info__title" data-anim="rise">
            Ein Raum für <span class="text-italic">echte Begegnung</span>
          </h2>
          <p class="no-event-info__text" data-anim="rise">Der Männerkreis Niederbayern/ Straubing bietet dir einen geschützten Raum, in dem du dich mit anderen Männern austauschen, wachsen und echte Verbindungen aufbauen kannst. Unsere Treffen finden regelmäßig statt – sobald der nächste Termin feststeht, informieren wir dich.</p>
        </div>
        <div class="no-event-info__visual" data-anim="scale">
          <div class="no-event-info__quote-area">
            <div class="event-about__circles" aria-hidden="true">
              <div class="event-about__circle event-about__circle--1"></div>
              <div class="event-about__circle event-about__circle--2"></div>
              <div class="event-about__circle event-about__circle--3"></div>
            </div>
            <p class="event-about__quote">»Bleib<br />
            <span class="text-italic">verbunden</span>«</p>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- Newsletter Section -->
  <section class="section newsletter-section" id="newsletter">
    <div class="container">
      <div class="newsletter__layout">
        <div class="newsletter__content" data-anim-group>
          <p class="eyebrow eyebrow--secondary" data-anim="rise">Newsletter</p>
          <h2 class="section-title newsletter__title" data-anim="rise">
            Bleib <span class="text-italic">informiert</span>
          </h2>
          <p class="newsletter__text" data-anim="rise">Erhalte als Erster Bescheid, wenn unser nächstes Treffen stattfindet. Kein Spam, nur relevante Informationen zum Männerkreis.</p>
        </div>

        <div class="newsletter__form-wrapper" data-anim="scale">
          <form id="newsletterForm" class="newsletter__form">
            <input
              type="email"
              name="email"
              placeholder="Deine E-Mail-Adresse"
              required
              class="newsletter__input"
              aria-label="E-Mail-Adresse"
            />
            <button type="submit" class="btn btn--primary">Anmelden</button>
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
      <div class="no-event-cta__content" data-anim-group>
        <p class="eyebrow" data-anim="rise">Mehr erfahren</p>
        <h2 class="section-title no-event-cta__title" data-anim="rise">
          Entdecke den <span class="text-italic">Männerkreis</span>
        </h2>
        <p class="no-event-cta__text" data-anim="rise">Erfahre mehr über uns, unsere Werte und was dich bei einem Treffen erwartet.</p>
        <a
          href="{{ route('home') }}"
          class="btn btn--primary btn--large"
          data-anim="rise"
          >Zur Startseite</a
        >
      </div>
    </div>
  </section>
@endsection
