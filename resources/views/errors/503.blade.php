@extends ('layouts.app')

@section ('title', 'Wartungsarbeiten – Männerkreis Niederbayern/ Straubing')
@section ('meta_description', 'Wir führen gerade Wartungsarbeiten durch.')
@section ('robots', 'noindex, nofollow')

@section ('content')
  <section class="section-y-lg">
    <div class="container-narrow text-center">
      <p class="font-display text-[12rem] font-semibold leading-none text-[var(--color-warning)]/30" aria-hidden="true">503</p>
      <h1 class="-mt-12 font-display text-4xl font-semibold md:text-5xl">
        Wartungsarbeiten
      </h1>
      <p class="mt-4 text-lg text-[var(--fg-muted)]">Wir führen gerade Wartungsarbeiten durch.<br />Die Website ist in Kürze wieder verfügbar.</p>
      <p class="mt-6 text-[var(--fg-muted)]">
        Bei dringenden Fragen erreichst du uns per E-Mail:<br />
        <a
          href="mailto:hallo@mens-circle.de"
          class="underline hover:text-[var(--accent)]"
          >hallo@mens-circle.de</a
        >
      </p>
    </div>
  </section>
@endsection
