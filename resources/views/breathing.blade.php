@extends ('layouts.app')

@section ('title', 'Interaktive Atmung – Männerkreis Niederbayern/ Straubing')
@section ('meta_title', 'Interaktive Atmung – Männerkreis Niederbayern/ Straubing')
@section ('meta_description', 'Eine einfache interaktive Atem-App für bewusste Atemrunden mit Einatmen, Halten und Ausatmen.')
@section ('canonical', route('breathing'))
@section ('og_title', 'Interaktive Atmung – Männerkreis Niederbayern/ Straubing')
@section ('og_description', 'Starte eine geführte Atemrunde und finde mit einem klaren Rhythmus mehr Ruhe und Präsenz.')

@php
    use App\Seo\Data\BreadcrumbItem;
    use App\Seo\Schemas\BreadcrumbSchema;
    use App\Seo\Schemas\WebPageSchema;
@endphp

@push ('structured_data')
  {!! (new BreadcrumbSchema([
        new BreadcrumbItem('Startseite', route('home')),
        new BreadcrumbItem('Atmung', route('breathing')),
    ]))->toScript() !!}
  {!! (new WebPageSchema(
        title: 'Interaktive Atmung',
        description: 'Eine einfache interaktive Atem-App für bewusste Atemrunden mit Einatmen, Halten und Ausatmen.',
    ))->toScript() !!}
@endpush

@section ('content')
  <section class="hero breathing-hero">
    <div class="hero__bg"></div>
    <div class="container">
      <div class="hero__content">
        <p class="hero__label">Atem & Präsenz</p>
        <h1 class="hero__title">
          Interaktive <span class="highlight">Atemreise</span>
        </h1>
        <p class="hero__subtitle">
          Folge dem Rhythmus, finde Ruhe und nimm dir ein paar bewusste Minuten nur für dich.
        </p>
      </div>
    </div>
  </section>

  <section class="section breathing-section">
    <div class="container">
      <div class="breathing-grid">
        <div
          id="breathingApp"
          class="breathing-app breathing-app--idle fade-in"
          data-default-inhale="4"
          data-default-hold="4"
          data-default-exhale="6"
          data-default-pause="2"
          data-default-rounds="10"
        >
          <div class="breathing-app__panel">
            <div class="breathing-app__status">
              <span class="breathing-app__eyebrow">Geführte Runde</span>
              <p class="breathing-app__phase" data-breathing-phase aria-live="polite">
                Bereit
              </p>
              <p
                class="breathing-app__instruction"
                data-breathing-instruction
                aria-live="polite"
              >
                Starte, wenn du bereit bist, und atme weich und gleichmäßig.
              </p>
            </div>

            <div class="breathing-app__visual" aria-hidden="true">
              <div class="breathing-app__ring"></div>
              <div class="breathing-app__orb">
                <span
                  class="breathing-app__timer"
                  data-breathing-timer
                  aria-live="polite"
                  aria-atomic="true"
                >
                  4
                </span>
                <span class="breathing-app__timer-label">Sek.</span>
              </div>
            </div>

            <div class="breathing-app__meta">
              <div class="breathing-app__meta-item">
                <span>Runde</span>
                <strong data-breathing-round>0 / 10</strong>
              </div>
              <div class="breathing-app__meta-item">
                <span>Status</span>
                <strong data-breathing-state>Wartet auf Start</strong>
              </div>
            </div>

            <div class="breathing-app__controls">
              <button type="button" class="btn btn--primary" data-breathing-start>
                Start
              </button>
              <button type="button" class="btn btn--secondary" data-breathing-pause>
                Pause
              </button>
              <button type="button" class="btn btn--secondary" data-breathing-reset>
                Reset
              </button>
            </div>
          </div>

          <div class="breathing-app__settings">
            <div class="breathing-app__setting">
              <label for="breathingInhale">Einatmen</label>
              <div class="breathing-app__setting-control">
                <input
                  id="breathingInhale"
                  type="range"
                  min="2"
                  max="8"
                  step="1"
                  value="4"
                  data-breathing-input="inhale"
                />
                <output data-breathing-output="inhale">4s</output>
              </div>
            </div>

            <div class="breathing-app__setting">
              <label for="breathingHold">Halten oben</label>
              <div class="breathing-app__setting-control">
                <input
                  id="breathingHold"
                  type="range"
                  min="0"
                  max="8"
                  step="1"
                  value="4"
                  data-breathing-input="hold"
                />
                <output data-breathing-output="hold">4s</output>
              </div>
            </div>

            <div class="breathing-app__setting">
              <label for="breathingExhale">Ausatmen</label>
              <div class="breathing-app__setting-control">
                <input
                  id="breathingExhale"
                  type="range"
                  min="2"
                  max="10"
                  step="1"
                  value="6"
                  data-breathing-input="exhale"
                />
                <output data-breathing-output="exhale">6s</output>
              </div>
            </div>

            <div class="breathing-app__setting">
              <label for="breathingPause">Halten unten</label>
              <div class="breathing-app__setting-control">
                <input
                  id="breathingPause"
                  type="range"
                  min="0"
                  max="8"
                  step="1"
                  value="2"
                  data-breathing-input="pause"
                />
                <output data-breathing-output="pause">2s</output>
              </div>
            </div>

            <div class="breathing-app__setting">
              <label for="breathingRounds">Runden</label>
              <div class="breathing-app__setting-control">
                <input
                  id="breathingRounds"
                  type="range"
                  min="1"
                  max="20"
                  step="1"
                  value="10"
                  data-breathing-input="rounds"
                />
                <output data-breathing-output="rounds">10</output>
              </div>
            </div>
          </div>
        </div>

        <aside class="breathing-info fade-in">
          <h2>So nutzt du die Atem-App</h2>
          <ol class="breathing-info__list">
            <li>Setze dich aufrecht und entspannt hin.</li>
            <li>Wähle deinen Rhythmus für Einatmen, Halten und Ausatmen.</li>
            <li>Starte die Runde und folge dem Kreis und dem Countdown.</li>
            <li>Bleibe weich im Atem und pausiere jederzeit, wenn es sich nicht gut anfühlt.</li>
          </ol>

          <div class="breathing-info__note">
            <h3>Wichtiger Hinweis</h3>
            <p>
              Übe nur im Sitzen oder Liegen und nie im Wasser, beim Autofahren oder in anderen Situationen, in denen Schwindel gefährlich wäre.
            </p>
          </div>
        </aside>
      </div>
    </div>
  </section>
@endsection
