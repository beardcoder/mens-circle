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
              <span
                class="breathing-app__ring breathing-app__ring--1"
                aria-hidden="true"
              ></span>
              <span
                class="breathing-app__ring breathing-app__ring--2"
                aria-hidden="true"
              ></span>
              <span
                class="breathing-app__ring breathing-app__ring--3"
                aria-hidden="true"
              ></span>
              <span class="breathing-app__core" aria-hidden="true"></span>
              <span class="breathing-app__label">
                <span class="breathing-app__phase" data-element="phase"
                  >Bereit</span
                >
                <span class="breathing-app__counter" data-element="counter"
                  >3 Runden · 30 Atemzüge</span
                >
              </span>
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
              class="btn btn--primary breathing-app__start"
              data-element="start"
              aria-label="Atemübung starten"
              title="Atemübung starten"
              data-umami-event="breathing-start"
            >
              <svg
                data-element="startIcon"
                viewBox="0 0 24 24"
                fill="currentColor"
                aria-hidden="true"
              >
                <path d="M8 5.14v13.72a1 1 0 0 0 1.54.84l10.3-6.86a1 1 0 0 0 0-1.68L9.54 4.3A1 1 0 0 0 8 5.14Z" />
              </svg>
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
            <div class="breathing-app__setting breathing-app__setting--wheel">
              <span class="breathing-app__setting-label"
                >Atemzüge je Runde</span
              >
              <div
                class="breathing-wheel"
                data-element="settingBreaths"
                data-min="10"
                data-max="60"
                data-value="30"
                role="slider"
                tabindex="0"
                aria-label="Atemzüge je Runde"
                aria-valuemin="10"
                aria-valuemax="60"
                aria-valuenow="30"
              >
                <div
                  class="breathing-wheel__viewport"
                  data-element="settingBreathsViewport"
                >
                  <div
                    class="breathing-wheel__track"
                    data-element="settingBreathsTrack"
                  ></div>
                </div>
                <div
                  class="breathing-wheel__indicator"
                  aria-hidden="true"
                ></div>
                <div
                  class="breathing-wheel__fade breathing-wheel__fade--start"
                  aria-hidden="true"
                ></div>
                <div
                  class="breathing-wheel__fade breathing-wheel__fade--end"
                  aria-hidden="true"
                ></div>
              </div>
            </div>
            <div class="breathing-app__setting breathing-app__setting--stepper">
              <span class="breathing-app__setting-label">Runden</span>
              <div class="breathing-stepper">
                <button
                  type="button"
                  class="breathing-stepper__btn"
                  data-element="settingRoundsMinus"
                  aria-label="Eine Runde weniger"
                >
                  −
                </button>
                <span
                  class="breathing-stepper__value"
                  data-element="settingRounds"
                  data-min="1"
                  data-max="6"
                  data-value="3"
                  role="spinbutton"
                  aria-valuemin="1"
                  aria-valuemax="6"
                  aria-valuenow="3"
                  >3</span
                >
                <button
                  type="button"
                  class="breathing-stepper__btn"
                  data-element="settingRoundsPlus"
                  aria-label="Eine Runde mehr"
                >
                  +
                </button>
              </div>
            </div>
            <div class="breathing-app__setting breathing-app__setting--stepper">
              <span class="breathing-app__setting-label"
                >Erholungs-Halt (Sek.)</span
              >
              <div class="breathing-stepper">
                <button
                  type="button"
                  class="breathing-stepper__btn"
                  data-element="settingRecoveryMinus"
                  aria-label="Erholungs-Halt verringern"
                >
                  −
                </button>
                <span
                  class="breathing-stepper__value"
                  data-element="settingRecovery"
                  data-min="5"
                  data-max="30"
                  data-value="15"
                  role="spinbutton"
                  aria-valuemin="5"
                  aria-valuemax="30"
                  aria-valuenow="15"
                  >15</span
                >
                <button
                  type="button"
                  class="breathing-stepper__btn"
                  data-element="settingRecoveryPlus"
                  aria-label="Erholungs-Halt erhöhen"
                >
                  +
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
