@extends('layouts.app')

@section('title', 'Impressum – Männerkreis Niederbayern/ Straubing')
@section('meta_description', 'Impressum und rechtliche Angaben des Männerkreis Niederbayern/ Straubing gemäß § 5 TMG.')
@section('robots', 'index, follow')

<x-seo.breadcrumb-schema :items="[
    ['name' => 'Startseite', 'url' => route('home')],
    ['name' => 'Impressum', 'url' => route('page.show', 'impressum')],
]" />

<x-seo.webpage-schema
    title="Impressum"
    description="Impressum und rechtliche Angaben des Männerkreis Niederbayern/ Straubing gemäß § 5 TMG."
/>

@section('content')
    <section class="section">
        <div class="container container--narrow">
            <h1>Impressum</h1>

            <h2>Angaben gemäß § 5 TMG</h2>
            <p>
                <strong>WICHTIG: Vor Go-Live vervollständigen!</strong><br><br>
                Markus Sommer<br>
                Männerkreis Niederbayern/ Straubing<br>
                Musterstraße 123<br>
                94315 Straubing
            </p>

            <h2>Kontakt</h2>
            <p>
                E-Mail: <a href="mailto:hallo@mens-circle.de">hallo@mens-circle.de</a>
            </p>

            <h2>Haftungsausschluss</h2>
            <h3>Haftung für Inhalte</h3>
            <p>
                Die Inhalte unserer Seiten wurden mit größter Sorgfalt erstellt. Für die Richtigkeit,
                Vollständigkeit und Aktualität der Inhalte können wir jedoch keine Gewähr übernehmen.
            </p>

            <h3>Haftung für Links</h3>
            <p>
                Unser Angebot enthält Links zu externen Webseiten Dritter, auf deren Inhalte wir keinen
                Einfluss haben. Deshalb können wir für diese fremden Inhalte auch keine Gewähr übernehmen.
            </p>

            <h2>Urheberrecht</h2>
            <p>
                Die durch die Seitenbetreiber erstellten Inhalte und Werke auf diesen Seiten unterliegen
                dem deutschen Urheberrecht. Die Vervielfältigung, Bearbeitung, Verbreitung und jede Art
                der Verwertung außerhalb der Grenzen des Urheberrechtes bedürfen der schriftlichen
                Zustimmung des jeweiligen Autors bzw. Erstellers.
            </p>
        </div>
    </section>
@endsection
