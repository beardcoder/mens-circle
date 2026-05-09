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
    class="relative isolate overflow-hidden bg-[var(--bg-deep)] text-[var(--color-parchment)]"
  >
    <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
      <div
        class="absolute -top-20 -left-20 h-80 w-80 rounded-full bg-[var(--color-terracotta)]/30 blur-3xl animate-breathe"
      ></div>
    </div>
    <div class="container-page flex min-h-[50vh] flex-col justify-center py-20">
      <p x-reveal class="eyebrow text-[var(--color-terracotta-light)]">Community Stimmen</p>
      <h1
        x-reveal
        class="font-display text-4xl font-semibold leading-tight md:text-6xl"
      >
        Teile deine <span class="text-italic">Erfahrung</span>
      </h1>
      <p x-reveal class="mt-4 max-w-xl text-lg text-[var(--color-sand)]">Deine Geschichte kann anderen Männern Mut machen, den ersten Schritt zu wagen.</p>
    </div>
  </section>
  <section class="section-y">
    <div class="container-narrow">
      <div x-reveal class="prose-block mb-8 text-[var(--fg-muted)]">
        <h2 class="font-display text-2xl font-semibold text-[var(--fg)]">
          Deine Stimme zählt
        </h2>
        <p>Der Männerkreis lebt von authentischen Begegnungen. Wenn du Teil unserer Community bist und deine Erfahrung teilen möchtest, würden wir uns freuen, von dir zu hören.</p>
        <p>Dein Testimonial wird nach Prüfung auf unserer Website veröffentlicht und kann anderen Männern helfen zu verstehen, was der Kreis bedeuten kann.</p>
      </div>

      <form
        x-data="testimonialForm('{{ route('testimonial.submit') }}')"
        @submit.prevent="submit($event)"
        x-reveal
        class="flex flex-col gap-5 rounded-2xl bg-[var(--bg-alt)] p-8"
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
