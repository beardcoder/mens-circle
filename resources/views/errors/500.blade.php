@extends ('layouts.app')

@section ('title', 'Serverfehler – Männerkreis Niederbayern/ Straubing')
@section ('meta_description', 'Es ist ein unerwarteter Fehler aufgetreten.')
@section ('robots', 'noindex, nofollow')

@section ('content')
  <section class="section-y-lg">
    <div class="container-narrow text-center">
      <p class="font-display text-[12rem] font-semibold leading-none text-[var(--color-error)]/20" aria-hidden="true">500</p>
      <h1 class="-mt-12 font-display text-4xl font-semibold md:text-5xl">
        Serverfehler
      </h1>
      <p class="mt-4 text-lg text-[var(--fg-muted)]">Es ist ein unerwarteter Fehler aufgetreten. Wir arbeiten bereits an einer Lösung.<br />Bitte versuche es in wenigen Minuten erneut.</p>
      <div class="mt-8 flex flex-wrap justify-center gap-3">
        <a href="{{ route('home') }}" class="btn btn-primary">Zur Startseite</a>
        <a href="mailto:hallo@mens-circle.de" class="btn btn-secondary"
          >Kontakt aufnehmen</a
        >
      </div>
    </div>
  </section>
@endsection
