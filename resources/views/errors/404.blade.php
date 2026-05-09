@extends ('layouts.app')

@section ('title', 'Seite nicht gefunden – Männerkreis Niederbayern/ Straubing')
@section ('meta_description', 'Die gesuchte Seite existiert nicht oder wurde verschoben.')
@section ('robots', 'noindex, nofollow')

@section ('content')
  <section class="section-y-lg">
    <div class="container-narrow text-center">
      <p class="font-display text-[12rem] font-semibold leading-none text-[var(--accent)]/20" aria-hidden="true">404</p>
      <h1 class="-mt-12 font-display text-4xl font-semibold md:text-5xl">
        Seite nicht gefunden
      </h1>
      <p class="mt-4 text-lg text-[var(--fg-muted)]">Die gesuchte Seite existiert leider nicht oder wurde verschoben.</p>
      <div class="mt-8 flex flex-wrap justify-center gap-3">
        <a href="{{ route('home') }}" class="btn btn-primary">Zur Startseite</a>
        @if ($hasNextEvent ?? false)
          <a href="{{ route('event.show') }}" class="btn btn-secondary"
            >Zum nächsten Termin</a
          >
        @endif
      </div>
    </div>
  </section>
@endsection
