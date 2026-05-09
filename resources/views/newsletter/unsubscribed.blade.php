@extends ('layouts.app')

@section ('title', 'Newsletter abgemeldet – Männerkreis Niederbayern/ Straubing')
@section ('meta_description', 'Du hast dich erfolgreich von unserem Newsletter abgemeldet.')
@section ('robots', 'noindex, nofollow')

@section ('content')
  <section class="section-y-lg">
    <div class="container-narrow text-center">
      <h1 class="font-display text-4xl font-semibold md:text-5xl">
        Newsletter abgemeldet
      </h1>
      <p class="mt-4 text-lg text-[var(--fg-muted)]">{{ $message }}</p>
      <a href="{{ route('home') }}" class="btn btn-primary mt-8"
        >Zurück zur Startseite</a
      >
    </div>
  </section>
@endsection
