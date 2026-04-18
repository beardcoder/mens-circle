@extends ('layouts.app')

@section ('title', 'Atemübung – Männerkreis Niederbayern/ Straubing')
@section ('meta_description', 'Eine geführte, interaktive Atemübung im Stil der Wim-Hof-Methode. Drei Runden bewusster Atem für Klarheit, Energie und innere Ruhe.')
@section ('og_title', 'Atemübung – Männerkreis Niederbayern/ Straubing')

@php
    use App\Seo\Data\BreadcrumbItem;
    use App\Seo\Schemas\BreadcrumbSchema;
    use App\Seo\Schemas\WebPageSchema;
@endphp

@push ('structured_data')
  {!! (new BreadcrumbSchema([
        new BreadcrumbItem('Startseite', route('home')),
        new BreadcrumbItem('Atemübung', route('breathing.show')),
    ]))->toScript() !!}
  {!! (new WebPageSchema(
        title: 'Atemübung',
        description: 'Eine geführte, interaktive Atemübung im Stil der Wim-Hof-Methode.',
    ))->toScript() !!}
@endpush

@section ('content')
  <!-- Breathing Hero -->
  <section class="hero breathing-hero">
    <div class="hero__bg"></div>
    <div class="container">
      <div class="hero__content">
        <p class="hero__label">Bewusster Atem</p>
        <h1 class="hero__title">Atem<span class="highlight">übung</span></h1>
        <p class="hero__subtitle">Drei Runden bewusster Atem im Stil der Wim-Hof-Methode. Für Klarheit, Energie und innere Ruhe.</p>
      </div>
    </div>
  </section>
  <!-- Breathing Section -->
  <section class="section breathing-section">
    <div class="container">
      <div class="breathing__wrapper">
        <div class="breathing__intro fade-in">
          <h2>So funktioniert es</h2>
          <ol class="breathing__steps">
            <li>
              <strong>Tief atmen:</strong> 30 kräftige Atemzüge — vollständig
              einatmen, locker ausatmen.
            </li>
            <li>
              <strong>Halten:</strong> Nach der letzten Ausatmung den Atem so
              lange wie möglich anhalten.
            </li>
            <li>
              <strong>Erholung:</strong> Tief einatmen und 15 Sekunden halten.
              Dann normal weiteratmen.
            </li>
          </ol>
          <p class="breathing__warning">
            <strong>Wichtig:</strong> Übe niemals im Wasser oder beim
            Autofahren. Setze oder lege dich entspannt hin.
          </p>
        </div>

        <div
          id="breathingApp"
          class="breathing-app fade-in"
          data-breaths="30"
          data-rounds="3"
          data-recovery-hold="15"
          data-inhale-ms="1800"
          data-exhale-ms="1800"
        >
          <div class="breathing-app__stage" aria-live="polite">
            <div class="breathing-app__circle" data-element="circle">
              <span class="breathing-app__phase" data-element="phase"
                >Bereit</span
              >
              <span class="breathing-app__counter" data-element="counter"
                >3 Runden · 30 Atemzüge</span
              >
            </div>
          </div>

          <div class="breathing-app__meta">
            <div class="breathing-app__meta-item">
              <span class="breathing-app__meta-label">Runde</span>
              <span class="breathing-app__meta-value" data-element="round"
                >0&nbsp;/&nbsp;3</span
              >
            </div>
            <div class="breathing-app__meta-item">
              <span class="breathing-app__meta-label">Atemzug</span>
              <span class="breathing-app__meta-value" data-element="breath"
                >0&nbsp;/&nbsp;30</span
              >
            </div>
            <div class="breathing-app__meta-item">
              <span class="breathing-app__meta-label">Zeit</span>
              <span class="breathing-app__meta-value" data-element="timer"
                >00:00</span
              >
            </div>
          </div>

          <div class="breathing-app__controls">
            <button
              type="button"
              class="btn btn--primary"
              data-element="start"
              data-umami-event="breathing-start"
            >
              Start
            </button>
            <button
              type="button"
              class="btn btn--secondary"
              data-element="hold"
              hidden
              data-umami-event="breathing-resume"
            >
              Atem freigeben
            </button>
            <button
              type="button"
              class="btn btn--ghost"
              data-element="reset"
              data-umami-event="breathing-reset"
            >
              Zurücksetzen
            </button>
          </div>

          <div class="breathing-app__settings">
            <label class="breathing-app__setting">
              <span>Atemzüge je Runde</span>
              <input
                type="number"
                data-element="settingBreaths"
                min="10"
                max="60"
                step="1"
                value="30"
              />
            </label>
            <label class="breathing-app__setting">
              <span>Runden</span>
              <input
                type="number"
                data-element="settingRounds"
                min="1"
                max="6"
                step="1"
                value="3"
              />
            </label>
            <label class="breathing-app__setting">
              <span>Erholungs-Halt (Sek.)</span>
              <input
                type="number"
                data-element="settingRecovery"
                min="5"
                max="30"
                step="1"
                value="15"
              />
            </label>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
