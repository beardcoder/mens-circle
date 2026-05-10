@extends ('layouts.app')

@section ('title', $event->title . ' am ' . $event->event_date->format('d.m.Y') . ' – Männerkreis Niederbayern/ Straubing')
@section ('meta_description', $event->isPast
    ? 'Rückblick auf das Treffen des Männerkreis Niederbayern/ Straubing: ' . $event->title . ' am ' . $event->event_date->format('d.m.Y')
    : 'Melde dich jetzt für das nächste Treffen des Männerkreis Niederbayern/ Straubing an: ' . $event->title . ' am ' . $event->event_date->format('d.m.Y'))
@section ('og_type', 'event')
@section ('og_title', $event->title . ' am ' . $event->event_date->format('d.m.Y') . ' – Männerkreis Niederbayern/ Straubing')
@section ('og_description', 'Treffen des Männerkreis Niederbayern/ Straubing am ' . $event->event_date->format('d.m.Y') . ' in ' . $event->location)
@section ('canonical', route('event.show.slug', $event->slug))

@push ('structured_data')
  {!! $eventSchema->toScript() !!}
  {!! $breadcrumbSchema->toScript() !!}
@endpush

@section ('content')
  {{-- Event Hero --}}
  <section
    data-hero
    class="relative isolate flex min-h-[90svh] items-end overflow-hidden bg-gradient-to-b from-[var(--color-earth-deep)] to-[var(--color-earth-dark)] pb-24 text-[var(--color-parchment)]"
    style="min-block-size: min(800px, 90svh)"
  >
    @if ($eventImage)
      {{ $eventImage->img()->attributes([
          'class' => 'absolute inset-0 -z-10 h-full w-full object-cover opacity-25 mix-blend-luminosity',
          'loading' => 'eager',
          'fetchpriority' => 'high',
          'aria-hidden' => 'true',
          'alt' => $event->title,
      ]) }}
    @endif
    <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
      <div
        class="absolute inset-0 [background:radial-gradient(ellipse_80%_60%_at_70%_30%,color-mix(in_oklch,var(--accent)_22%,transparent)_0%,transparent_50%),radial-gradient(ellipse_60%_50%_at_20%_80%,color-mix(in_oklch,var(--accent)_12%,transparent)_0%,transparent_40%)]"
      ></div>
    </div>
    <div class="hero-decor" aria-hidden="true">
      <span
        class="-top-[15vw] -right-[25vw] h-[70vw] w-[70vw] animate-breathe [animation-duration:18s]"
      ></span>
      <span
        class="-top-[5vw] -right-[15vw] h-[50vw] w-[50vw] animate-breathe [animation-delay:-5s] [animation-duration:22s]"
      ></span>
      <span
        class="-bottom-[40vw] -left-[40vw] h-[80vw] w-[80vw] animate-breathe [animation-delay:-3s] [animation-duration:30s]"
      ></span>
    </div>

    <div class="container-page relative z-10 w-full">
      <p x-reveal class="eyebrow text-[var(--color-terracotta-light)]">{{ $event->isPast ? 'Vergangenes Treffen' : 'Nächstes Treffen' }}</p>
      <h1 x-reveal class="hero-title">{{ $event->title }}</h1>
      <div
        class="mt-10 flex flex-col gap-6 md:flex-row md:items-end md:justify-between"
      >
        <p x-reveal class="max-w-[520px] text-base leading-[1.9] text-[var(--color-sand)] md:text-lg">
          {{ $event->event_date->translatedFormat('l') }}, {{ $event->event_date->format('d.m.Y') }} · {{ $event->start_time->format('H:i') }} Uhr
          · {{ $event->location }}
        </p>
        @unless ($event->isPast)
          <a
            x-reveal
            href="#anmeldung"
            class="btn btn-primary btn-large"
            data-umami-event="cta-click"
            data-umami-event-location="hero"
            data-umami-event-action="scroll-to-registration"
            >Jetzt anmelden</a
          >
        @endunless
      </div>
    </div>
  </section>
  {{-- Registration / Past info --}}
  <section
    class="relative isolate overflow-hidden bg-gradient-to-br from-[var(--color-earth-deep)] to-[var(--color-earth-dark)] text-[var(--color-parchment)]"
    id="anmeldung"
  >
    <span
      class="pointer-events-none absolute -top-[10vw] -left-[10vw] block h-[40vw] w-[40vw] rounded-full border border-[var(--color-sand)]/15 animate-breathe [animation-duration:24s]"
      aria-hidden="true"
    ></span>
    <span
      class="pointer-events-none absolute bottom-0 right-0 block h-[60vw] w-[60vw] translate-x-1/3 translate-y-1/3 rounded-full border border-[var(--color-sand)]/10 animate-breathe [animation-delay:-6s] [animation-duration:30s]"
      aria-hidden="true"
    ></span>
    <span
      class="pointer-events-none absolute inset-0 -z-10 [background:radial-gradient(ellipse_70%_60%_at_30%_30%,color-mix(in_oklch,var(--accent)_18%,transparent)_0%,transparent_50%)]"
      aria-hidden="true"
    ></span>

    <div
      class="container-page section-y grid gap-12 md:grid-cols-[1fr_1.1fr] md:items-center"
    >
      <div x-reveal>
        @if ($event->isPast)
          <p class="eyebrow text-[var(--color-terracotta-light)]">Rückblick</p>
          <h2 class="section-title-lg text-[var(--color-parchment)]">
            Dieses Treffen <br /><span class="text-italic"
              >hat stattgefunden</span
            >
          </h2>
          <p class="mt-6 text-lg text-[var(--color-sand)]">Am {{ $event->event_date->format('d.m.Y') }}</p>
        @elseif ($event->isFull)
          <p class="eyebrow text-[var(--color-terracotta-light)]">Warteliste</p>
          <h2 class="section-title-lg text-[var(--color-parchment)]">
            Trag dich auf die <br /><span class="text-italic"
              >Warteliste ein</span
            >
          </h2>
          <p class="mt-6 inline-flex rounded-full bg-[var(--color-error)]/20 px-4 py-1.5 text-sm font-medium text-[var(--color-error)]">Ausgebucht</p>
          <p class="mt-3 max-w-md text-sm leading-relaxed text-[var(--color-sand)]">Bei Absagen rückt die Warteliste automatisch nach. Du wirst sofort per E-Mail informiert.</p>
        @else
          <p class="eyebrow text-[var(--color-terracotta-light)]">Sei dabei</p>
          <h2 class="section-title-lg text-[var(--color-parchment)]">
            Sichere dir <br /><span class="text-italic">deinen Platz</span>
          </h2>
          <p class="mt-6 text-lg text-[var(--color-sand)]"><span class="font-display text-3xl font-semibold text-[var(--color-terracotta-light)]">{{ $event->availableSpots }}</span> von {{ $event->max_participants }} Plätzen frei</p>
        @endif
      </div>

      @unless ($event->isPast)
        <form
          x-data="registrationForm({{ $event->id }})"
          @submit.prevent="submit($event)"
          x-reveal
          class="flex flex-col gap-5 rounded-3xl bg-[var(--bg)] p-8 text-[var(--fg)] shadow-[0_20px_40px_-10px_color-mix(in_oklch,var(--color-ink)_25%,transparent)] md:p-10"
          autocomplete="on"
        >
          <input type="hidden" name="event_id" value="{{ $event->id }}" />

          <div class="grid gap-4 sm:grid-cols-2">
            <label class="flex flex-col gap-1.5 text-sm">
              <span class="font-medium">Vorname</span>
              <input
                type="text"
                x-model="firstName"
                name="first_name"
                placeholder="Dein Vorname"
                required
                autocomplete="given-name"
                class="rounded-lg border border-[var(--border)] bg-[var(--bg)] px-4 py-2.5 focus:border-[var(--accent)] focus:outline-none"
              />
            </label>
            <label class="flex flex-col gap-1.5 text-sm">
              <span class="font-medium">Nachname</span>
              <input
                type="text"
                x-model="lastName"
                name="last_name"
                placeholder="Dein Nachname"
                required
                autocomplete="family-name"
                class="rounded-lg border border-[var(--border)] bg-[var(--bg)] px-4 py-2.5 focus:border-[var(--accent)] focus:outline-none"
              />
            </label>
          </div>

          <label class="flex flex-col gap-1.5 text-sm">
            <span class="font-medium">E-Mail</span>
            <input
              type="email"
              x-model="email"
              name="email"
              placeholder="deine@email.de"
              required
              autocomplete="email"
              inputmode="email"
              class="rounded-lg border border-[var(--border)] bg-[var(--bg)] px-4 py-2.5 focus:border-[var(--accent)] focus:outline-none"
            />
          </label>

          <label class="flex flex-col gap-1.5 text-sm">
            <span class="font-medium"
              >Handynummer
              <span class="text-[var(--fg-muted)] font-normal"
                >(optional)</span
              ></span
            >
            <input
              type="tel"
              x-model="phone"
              name="phone_number"
              placeholder="+49 170 1234567"
              autocomplete="tel"
              inputmode="tel"
              class="rounded-lg border border-[var(--border)] bg-[var(--bg)] px-4 py-2.5 focus:border-[var(--accent)] focus:outline-none"
            />
            <span class="text-xs text-[var(--fg-muted)]"
              >Für Erinnerungen per SMS am Veranstaltungstag</span
            >
          </label>

          <label class="flex items-start gap-3 text-sm text-[var(--fg-muted)]">
            <input
              type="checkbox"
              x-model="privacy"
              name="privacy"
              required
              class="mt-1 h-4 w-4 rounded border-[var(--border)] text-[var(--accent)] focus:ring-[var(--accent)]"
            />
            <span
              >Ich habe die
              <a
                href="{{ route('page.show', 'datenschutz') }}"
                target="_blank"
                class="underline hover:text-[var(--accent)]"
                >Datenschutzerklärung</a
              >
              gelesen und stimme der Verarbeitung meiner Daten zu.</span
            >
          </label>

          <button type="submit" class="btn btn-primary btn-large mt-2">
            {{ $event->isFull ? 'Auf Warteliste eintragen' : 'Verbindlich anmelden' }}
          </button>
        </form>
      @else
        <div
          x-reveal
          class="flex flex-col gap-4 rounded-3xl bg-[var(--bg)] p-8 text-[var(--fg)] md:p-10"
        >
          <p class="text-[var(--fg-muted)]">Dieses Treffen liegt in der Vergangenheit. Eine Anmeldung ist nicht mehr möglich.</p>
          <p class="text-[var(--fg-muted)]">Möchtest du beim nächsten Männerkreis dabei sein? Dann trag dich in unseren Newsletter ein.</p>
          <a
            href="{{ route('home') }}#newsletter"
            class="btn btn-primary btn-large self-start"
            >Zum Newsletter anmelden</a
          >
        </div>
      @endunless
    </div>
  </section>
  {{-- Event info cards --}}
  <section class="section-y relative isolate overflow-hidden">
    <span
      class="pointer-events-none absolute inset-x-0 top-1/2 -z-10 -translate-y-1/2 select-none text-center font-display text-[clamp(7rem,18vw,16rem)] font-medium tracking-tight text-[var(--fg)]/[0.04]"
      aria-hidden="true"
      >TERMIN</span
    >

    <div class="container-page">
      <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['Datum', $event->event_date->translatedFormat('l'), $event->event_date->format('d. F Y')],
            ['Uhrzeit', $event->start_time->format('H:i') . ' Uhr', 'bis ' . $event->end_time->format('H:i') . ' Uhr'],
            ['Ort', $event->location, 'Genaue Adresse nach Anmeldung'],
            ['Teilnehmer', 'Max. ' . $event->max_participants, $event->cost_basis],
        ] as $card)
          <div
            x-reveal
            class="group relative isolate overflow-hidden rounded-2xl border border-[var(--border)] bg-[var(--bg)] p-7 shadow-[0_2px_4px_color-mix(in_oklch,var(--color-ink)_4%,transparent),0_12px_24px_-6px_color-mix(in_oklch,var(--color-ink)_8%,transparent)] transition-transform duration-500 hover:-translate-y-1"
          >
            <span
              class="pointer-events-none absolute -right-6 -top-6 h-24 w-24 rounded-full bg-gradient-to-br from-[var(--accent)]/15 to-transparent blur-xl transition-opacity duration-500 group-hover:opacity-100"
              aria-hidden="true"
            ></span>
            <h3
              class="text-[0.7rem] font-semibold uppercase tracking-[0.22em] text-[var(--accent)]"
            >
              {{ $card[0] }}
            </h3>
            <p class="mt-4 font-display text-2xl font-medium leading-tight">{{ $card[1] }}</p>
            <p class="mt-2 text-sm text-[var(--fg-muted)]">{{ $card[2] }}</p>
          </div>
        @endforeach
      </div>

      <div
        x-data="calendar"
        data-event-title="{{ $event->title }}"
        data-event-description="{{ strip_tags($event->description) }}"
        data-event-location="{{ $event->location }}"
        data-event-start-date="{{ $event->event_date->format('Y-m-d') }}"
        data-event-start-time="{{ $event->start_time->format('H:i') }}"
        data-event-end-date="{{ $event->event_date->format('Y-m-d') }}"
        data-event-end-time="{{ $event->end_time->format('H:i') }}"
        class="mt-10"
      >
        <button type="button" @click="open" class="btn btn-secondary">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
            <line x1="16" y1="2" x2="16" y2="6" />
            <line x1="8" y1="2" x2="8" y2="6" />
            <line x1="3" y1="10" x2="21" y2="10" />
          </svg>
          In Kalender speichern
        </button>

        {{-- Calendar modal --}}
        <div
          x-show="show"
          x-transition.opacity
          @click.self="close"
          @keydown.escape.window="close"
          class="fixed inset-0 z-[1300] grid place-items-center bg-black/60 p-6 backdrop-blur-sm"
          style="display: none"
        >
          <div class="w-full max-w-md rounded-2xl bg-[var(--bg)] p-8 shadow-xl">
            <h3 class="font-display text-2xl font-semibold">
              In Kalender speichern
            </h3>
            <p class="mt-2 text-[var(--fg-muted)]">Wähle deinen Kalender:</p>
            <div class="mt-6 flex flex-col gap-3">
              <a
                :href="googleUrl"
                @click="trackGoogle"
                target="_blank"
                rel="noopener"
                class="btn btn-secondary"
                >Google Calendar</a
              >
              <a
                :href="icsUrl"
                @click="trackIcs"
                download="maennerkreis-straubing.ics"
                class="btn btn-secondary"
                >Apple/Outlook (.ics)</a
              >
              <button type="button" @click="close" class="btn btn-ghost">
                Schließen
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  {{-- Map --}}
  @if ($event->hasCoordinates)
    <section class="section-y bg-[var(--bg-alt)]">
      <div class="container-page">
        <div x-reveal class="mb-8">
          <p class="eyebrow">Anfahrt</p>
          <h2 class="section-title-lg">
            So findest du <span class="text-italic">zu uns</span>
          </h2>
          <p class="mt-3 text-[var(--fg-muted)]">{{ $event->fullAddress ?? $event->location }}</p>
        </div>

        <div
          x-data="eventMap"
          data-lat="{{ $event->latitude }}"
          data-lng="{{ $event->longitude }}"
          data-title="{{ $event->location }}"
          data-address="{{ $event->fullAddress ?? $event->location }}"
          aria-label="Karte zum Veranstaltungsort"
          class="aspect-[16/9] overflow-hidden rounded-2xl border border-[var(--border)]"
        >
          <div
            data-map-canvas
            role="application"
            aria-label="Interaktive Karte"
            class="h-full w-full"
          ></div>
        </div>

        <div class="mt-6 flex flex-wrap gap-3">
          <a
            href="https://www.openstreetmap.org/?mlat={{ $event->latitude }}&mlon={{ $event->longitude }}#map=17/{{ $event->latitude }}/{{ $event->longitude }}"
            target="_blank"
            rel="noopener"
            class="btn btn-secondary"
            data-umami-event="map-click"
            data-umami-event-action="open-osm"
            >In OpenStreetMap öffnen</a
          >
          <a
            href="https://www.google.com/maps/dir/?api=1&destination={{ $event->latitude }},{{ $event->longitude }}"
            target="_blank"
            rel="noopener"
            class="btn btn-primary"
            data-umami-event="map-click"
            data-umami-event-action="get-directions"
            >Route mit Google Maps</a
          >
        </div>
        <p class="mt-4 text-xs text-[var(--fg-muted)]">Karte von <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener" class="underline">OpenStreetMap</a>-Mitwirkenden</p>
      </div>
    </section>
  @endif
  {{-- Description --}}
  <section class="section-y">
    <div class="container-page grid gap-12 md:grid-cols-2 md:items-center">
      <div x-reveal>
        <p class="eyebrow">Über das Treffen</p>
        <h2 class="section-title-lg">
          Ein Raum für <br /><span class="text-italic">echte Begegnung</span>
        </h2>
        <div class="prose-block mt-6 text-[var(--fg-muted)]">
          {!! nl2br(e($event->description)) !!}
        </div>
      </div>
      <div x-reveal.zoom class="relative aspect-square w-full max-w-md mx-auto">
        <div
          class="absolute inset-0 rounded-full bg-gradient-to-br from-[var(--color-terracotta)]/20 to-[var(--color-earth-warm)]/20 blur-2xl"
        ></div>
        <div
          class="absolute inset-6 rounded-full border border-[var(--border)] animate-breathe"
        ></div>
        <div
          class="relative grid h-full place-items-center text-center font-display text-2xl italic leading-snug"
        >
          »Gemeinsam<br /><span class="text-italic">wachsen</span
          >,<br />einander<br /><span class="text-italic">stärken</span>«
        </div>
      </div>
    </div>
  </section>
  {{-- Final CTA --}}
  <section class="section-y bg-[var(--bg-alt)]">
    <div class="container-page text-center">
      <div x-reveal class="mx-auto max-w-2xl">
        @if ($event->isPast)
          <p class="eyebrow">Interesse geweckt?</p>
          <h2 class="section-title-lg">
            Bleib <span class="text-italic">informiert</span>
          </h2>
          <a
            href="{{ route('home') }}#newsletter"
            class="btn btn-primary btn-large mt-8"
            data-umami-event="cta-click"
            data-umami-event-location="event-cta-bottom"
            data-umami-event-action="go-to-newsletter"
            >Newsletter abonnieren</a
          >
        @else
          <p class="eyebrow">Bereit?</p>
          <h2 class="section-title-lg">
            Melde dich <span class="text-italic">jetzt</span> an
          </h2>
          <a
            href="#anmeldung"
            class="btn btn-primary btn-large mt-8"
            data-umami-event="cta-click"
            data-umami-event-location="event-cta-bottom"
            data-umami-event-action="scroll-to-registration"
            >Zur Anmeldung</a
          >
        @endif
      </div>
    </div>
  </section>
@endsection
