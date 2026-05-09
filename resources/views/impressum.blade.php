@extends ('layouts.app')

@section ('title', 'Impressum – Männerkreis Niederbayern/ Straubing')
@section ('meta_description', 'Impressum und rechtliche Angaben des Männerkreis Niederbayern/ Straubing.')
@section ('robots', 'index, follow')

@php
    use App\Seo\Data\BreadcrumbItem;
    use App\Seo\Schemas\BreadcrumbSchema;
    use App\Seo\Schemas\WebPageSchema;
@endphp

@push ('structured_data')
  {!! (new BreadcrumbSchema([
        new BreadcrumbItem('Startseite', route('home')),
        new BreadcrumbItem('Impressum', route('page.show', 'impressum')),
    ]))->toScript() !!}
  {!! (new WebPageSchema(
        title: 'Impressum',
        description: 'Impressum und rechtliche Angaben.',
    ))->toScript() !!}
@endpush

@section ('content')
  <section class="section-y">
    <div class="container-narrow prose-block text-[var(--fg-muted)]">
      <h1 class="font-display text-4xl font-semibold text-[var(--fg)]">
        Impressum
      </h1>

      <h2>Angaben gemäß § 5 TMG</h2>
      <p>
        <strong>WICHTIG: Vor Go-Live vervollständigen!</strong><br /><br />
        Markus Sommer<br />
        Männerkreis Niederbayern/ Straubing<br />
        Musterstraße 123<br />
        94315 Straubing
      </p>

      <h2>Kontakt</h2>
      <p>E-Mail: <a href="mailto:hallo@mens-circle.de">hallo@mens-circle.de</a></p>

      <h2>Haftungsausschluss</h2>
      <h3>Haftung für Inhalte</h3>
      <p>Die Inhalte unserer Seiten wurden mit größter Sorgfalt erstellt. Für die Richtigkeit, Vollständigkeit und Aktualität der Inhalte können wir jedoch keine Gewähr übernehmen.</p>

      <h3>Haftung für Links</h3>
      <p>Unser Angebot enthält Links zu externen Webseiten Dritter, auf deren Inhalte wir keinen Einfluss haben.</p>

      <h2>Urheberrecht</h2>
      <p>Die durch die Seitenbetreiber erstellten Inhalte und Werke auf diesen Seiten unterliegen dem deutschen Urheberrecht.</p>
    </div>
  </section>
@endsection
