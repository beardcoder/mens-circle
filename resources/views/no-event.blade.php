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
    class="relative isolate flex min-h-[100svh] items-end overflow-hidden bg-gradient-to-b from-[var(--color-earth-deep)] to-[var(--color-earth-dark)] pb-24 text-[var(--color-parchment)]"
    style="min-block-size: min(880px, 100svh)"
  >
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
        class="-bottom-[45vw] -left-[45vw] h-[90vw] w-[90vw] animate-breathe [animation-delay:-3s] [animation-duration:30s]"
      ></span>
    </div>

    <div class="container-page relative z-10 w-full">
      <p x-reveal class="eyebrow text-[var(--color-terracotta-light)]">Männerkreis Niederbayern/ Straubing</p>
      <h1 x-reveal class="hero-title">
        <span class="hero-title-line">Aktuell ist kein</span>
        <span class="hero-title-line"
          ><span class="text-italic">Termin</span> geplant</span
        >
      </h1>
      <div
        class="mt-10 flex flex-col gap-6 md:flex-row md:items-end md:justify-between"
      >
        <p x-reveal class="max-w-[480px] text-base leading-[1.9] text-[var(--color-sand)] md:text-lg">Wir planen gerade unser nächstes Treffen. Melde dich für unseren Newsletter an oder tritt unserer WhatsApp-Community bei, um als Erster zu erfahren, wann es weitergeht.</p>
        <a x-reveal href="#newsletter" class="btn btn-primary btn-large"
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
