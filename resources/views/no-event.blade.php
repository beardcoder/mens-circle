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
        description: 'Derzeit ist kein Männerkreis-Treffen geplant.',
    ))->toScript() !!}
@endpush

@section ('content')
  <section
    data-hero
    class="relative isolate overflow-hidden bg-[var(--bg-deep)] text-[var(--color-parchment)]"
  >
    <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
      <div
        class="absolute -top-20 -left-20 h-80 w-80 rounded-full bg-[var(--color-terracotta)]/30 blur-3xl animate-breathe"
      ></div>
      <div
        class="absolute bottom-0 right-0 h-96 w-96 rounded-full bg-[var(--color-earth-warm)]/20 blur-3xl animate-breathe [animation-delay:3s]"
      ></div>
    </div>
    <div class="container-page flex min-h-[70vh] flex-col justify-center py-24">
      <p x-reveal class="eyebrow text-[var(--color-terracotta-light)]">Männerkreis Niederbayern/ Straubing</p>
      <h1
        x-reveal
        class="font-display text-4xl font-semibold leading-[1.05] md:text-6xl"
      >
        Aktuell ist kein<br /><span class="text-italic">Termin</span> geplant
      </h1>
      <p x-reveal class="mt-6 max-w-xl text-lg text-[var(--color-sand)] leading-relaxed">Wir planen gerade unser nächstes Treffen. Melde dich für unseren Newsletter an oder tritt unserer WhatsApp-Community bei, um als Erster zu erfahren, wann es weitergeht.</p>
      <div x-reveal class="mt-8">
        <a href="#newsletter" class="btn btn-primary btn-large"
          >Zum Newsletter</a
        >
      </div>
    </div>
  </section>
  <section class="section-y">
    <div class="container-page grid gap-12 md:grid-cols-2 md:items-center">
      <div x-reveal>
        <p class="eyebrow">Was ist der Männerkreis?</p>
        <h2 class="section-title-lg">
          Ein Raum für <span class="text-italic">echte Begegnung</span>
        </h2>
        <p class="mt-4 text-lg text-[var(--fg-muted)]">Der Männerkreis Niederbayern/ Straubing bietet dir einen geschützten Raum, in dem du dich mit anderen Männern austauschen, wachsen und echte Verbindungen aufbauen kannst.</p>
      </div>
      <div x-reveal.zoom class="relative aspect-square w-full max-w-md mx-auto">
        <div
          class="absolute inset-0 rounded-full bg-gradient-to-br from-[var(--color-terracotta)]/20 to-transparent blur-2xl"
        ></div>
        <div
          class="absolute inset-6 rounded-full border border-[var(--border)] animate-breathe"
        ></div>
        <p class="relative grid h-full place-items-center text-center font-display text-2xl italic">»Bleib<br /><span class="text-italic">verbunden</span>«</p>
      </div>
    </div>
  </section>
  <section
    class="section-y bg-[var(--bg-deep)] text-[var(--color-parchment)]"
    id="newsletter"
  >
    <div class="container-page grid gap-12 md:grid-cols-2 md:items-center">
      <div x-reveal>
        <p class="eyebrow text-[var(--color-terracotta-light)]">Newsletter</p>
        <h2 class="section-title-lg text-[var(--color-parchment)]">
          Bleib <span class="text-italic">informiert</span>
        </h2>
        <p class="mt-4 text-lg text-[var(--color-sand)]">Erhalte als Erster Bescheid, wenn unser nächstes Treffen stattfindet. Kein Spam.</p>
      </div>
      <form
        x-data="newsletterForm"
        @submit.prevent="submit($event)"
        x-reveal
        class="flex flex-col gap-3 sm:flex-row sm:items-center"
      >
        <label for="newsletter-email-noevent" class="sr-only"
          >E-Mail-Adresse</label
        >
        <input
          id="newsletter-email-noevent"
          type="email"
          x-model="email"
          name="email"
          placeholder="Deine E-Mail-Adresse"
          required
          class="flex-1 rounded-full border border-white/20 bg-white/10 px-5 py-3 text-[var(--color-parchment)] placeholder:text-[var(--color-sand)]/60 backdrop-blur-sm focus:border-[var(--color-terracotta-light)] focus:outline-none"
        />
        <button type="submit" class="btn btn-primary">Anmelden</button>
      </form>
    </div>
  </section>
  <x-blocks.whatsapp-community />
  <section class="section-y">
    <div class="container-page text-center">
      <div x-reveal class="mx-auto max-w-2xl">
        <p class="eyebrow">Mehr erfahren</p>
        <h2 class="section-title-lg">
          Entdecke den <span class="text-italic">Männerkreis</span>
        </h2>
        <p class="mt-4 text-lg text-[var(--fg-muted)]">Erfahre mehr über uns, unsere Werte und was dich bei einem Treffen erwartet.</p>
        <a href="{{ route('home') }}" class="btn btn-primary btn-large mt-8"
          >Zur Startseite</a
        >
      </div>
    </div>
  </section>
@endsection
