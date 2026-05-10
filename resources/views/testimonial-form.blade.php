@extends ('layouts.app')

@section ('title', 'Teile deine Erfahrung – Männerkreis Niederbayern/ Straubing')
@section ('meta_description', 'Teile deine Erfahrung mit dem Männerkreis Niederbayern/ Straubing.')
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
        description: 'Teile deine Erfahrung mit dem Männerkreis.',
    ))->toScript() !!}
@endpush

@section ('content')
  <section
    data-hero
    class="relative isolate flex min-h-[80svh] items-end overflow-hidden bg-gradient-to-b from-[var(--color-earth-deep)] to-[var(--color-earth-dark)] pb-20 text-[var(--color-parchment)]"
    style="min-block-size: min(720px, 80svh)"
  >
    <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
      <div
        class="absolute inset-0 [background:radial-gradient(ellipse_80%_60%_at_70%_30%,color-mix(in_oklch,var(--accent)_22%,transparent)_0%,transparent_50%)]"
      ></div>
    </div>
    <div class="hero-decor" aria-hidden="true">
      <span
        class="-top-[15vw] -right-[25vw] h-[70vw] w-[70vw] animate-breathe [animation-duration:18s]"
      ></span>
      <span
        class="-bottom-[40vw] -left-[40vw] h-[80vw] w-[80vw] animate-breathe [animation-delay:-3s] [animation-duration:30s]"
      ></span>
    </div>

    <div class="container-page relative z-10 w-full">
      <p class="eyebrow text-[var(--color-terracotta-light)] animate-reveal-up timeline-view animate-range-[entry_5%_cover_25%]">Community Stimmen</p>
      <h1
        class="hero-title animate-reveal-up timeline-view animate-range-[entry_5%_cover_25%]"
      >
        Teile deine <span class="text-italic">Erfahrung</span>
      </h1>
      <p class="mt-8 max-w-[520px] text-base leading-[1.9] text-[var(--color-sand)] md:text-lg animate-reveal-up timeline-view animate-range-[entry_5%_cover_25%]">Deine Geschichte kann anderen Männern Mut machen, den ersten Schritt zu wagen.</p>
    </div>
  </section>
  <section class="section-y">
    <div class="container-narrow">
      <div
        class="prose-block mb-8 text-[var(--fg-muted)] animate-reveal-up timeline-view animate-range-[entry_5%_cover_25%]"
      >
        <h2 class="font-display text-2xl font-semibold text-[var(--fg)]">
          Deine Stimme zählt
        </h2>
        <p>Der Männerkreis lebt von authentischen Begegnungen. Wenn du Teil unserer Community bist und deine Erfahrung teilen möchtest, würden wir uns freuen, von dir zu hören.</p>
        <p>Dein Testimonial wird nach Prüfung auf unserer Website veröffentlicht und kann anderen Männern helfen zu verstehen, was der Kreis bedeuten kann.</p>
      </div>

      <form
        x-data="testimonialForm('{{ route('testimonial.submit') }}')"
        @submit.prevent="submit($event)"
        class="flex flex-col gap-5 rounded-2xl bg-[var(--bg-alt)] p-8 animate-reveal-up timeline-view animate-range-[entry_5%_cover_25%]"
      >
        @csrf

        <label class="flex flex-col gap-1.5 text-sm">
          <span class="font-medium"
            >Deine Erfahrung
            <span class="text-[var(--color-error)]">*</span></span
          >
          <textarea
            x-model="quote"
            name="quote"
            rows="6"
            placeholder='z.B. "Hier kann ich endlich ich selbst sein, ohne Maske..."'
            required
            minlength="10"
            maxlength="1000"
            class="rounded-lg border border-[var(--border)] bg-[var(--bg)] px-4 py-3 leading-relaxed focus:border-[var(--accent)] focus:outline-none"
          ></textarea>
          <span
            class="flex items-center justify-between text-xs text-[var(--fg-muted)]"
          >
            <span>Mindestens 10 Zeichen, maximal 1000 Zeichen</span>
            <span><span x-text="charCount"></span>/1000</span>
          </span>
        </label>

        <label class="flex flex-col gap-1.5 text-sm">
          <span class="font-medium"
            >Dein Name
            <span class="text-[var(--fg-muted)] font-normal"
              >(optional)</span
            ></span
          >
          <input
            type="text"
            x-model="authorName"
            name="author_name"
            placeholder="z.B. Michael oder anonym lassen"
            maxlength="255"
            class="rounded-lg border border-[var(--border)] bg-[var(--bg)] px-4 py-2.5 focus:border-[var(--accent)] focus:outline-none"
          />
          <span class="text-xs text-[var(--fg-muted)]"
            >Leer lassen für ein anonymes Testimonial</span
          >
        </label>

        <label class="flex flex-col gap-1.5 text-sm">
          <span class="font-medium"
            >Rolle/Beschreibung
            <span class="text-[var(--fg-muted)] font-normal"
              >(optional)</span
            ></span
          >
          <input
            type="text"
            x-model="role"
            name="role"
            placeholder="z.B. Teilnehmer seit 2023"
            maxlength="255"
            class="rounded-lg border border-[var(--border)] bg-[var(--bg)] px-4 py-2.5 focus:border-[var(--accent)] focus:outline-none"
          />
        </label>

        <label class="flex flex-col gap-1.5 text-sm">
          <span class="font-medium"
            >E-Mail-Adresse
            <span class="text-[var(--color-error)]">*</span></span
          >
          <input
            type="email"
            x-model="email"
            name="email"
            placeholder="deine@email.de"
            required
            maxlength="255"
            class="rounded-lg border border-[var(--border)] bg-[var(--bg)] px-4 py-2.5 focus:border-[var(--accent)] focus:outline-none"
          />
          <span class="text-xs text-[var(--fg-muted)]"
            >Wird nicht veröffentlicht. Nur für Rückfragen.</span
          >
        </label>

        <label class="flex items-start gap-3 text-sm text-[var(--fg-muted)]">
          <input
            type="checkbox"
            x-model="privacy"
            name="privacy"
            required
            class="mt-1 h-4 w-4"
          />
          <span
            >Ich habe die
            <a
              href="/datenschutz"
              target="_blank"
              class="underline hover:text-[var(--accent)]"
              >Datenschutzerklärung</a
            >
            zur Kenntnis genommen und bin damit einverstanden, dass meine Daten
            zum Zwecke der Veröffentlichung gespeichert werden.
            <span class="text-[var(--color-error)]">*</span></span
          >
        </label>

        <button type="submit" class="btn btn-primary btn-large self-start">
          Erfahrung teilen
        </button>

        <p class="text-xs text-[var(--fg-muted)]">Alle Felder mit <span class="text-[var(--color-error)]">*</span> sind Pflichtfelder. Dein Testimonial wird nach Prüfung durch uns veröffentlicht.</p>
      </form>
    </div>
  </section>
@endsection
