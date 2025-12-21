<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Primary Meta Tags -->
    <title>@yield('title', 'Männerkreis Straubing – Gemeinschaft für Männer')</title>
    <meta name="title" content="@yield('meta_title', 'Männerkreis Straubing – Gemeinschaft für Männer')">
    <meta name="description" content="@yield('meta_description', 'Männerkreis Straubing – Authentischer Austausch, Gemeinschaft und persönliches Wachstum für Männer in Niederbayern.')">
    <meta name="keywords" content="@yield('meta_keywords', 'Männerkreis, Straubing, Niederbayern, Männergruppe, persönliches Wachstum, Gemeinschaft, Männer')">
    <meta name="author" content="Markus Sommer">
    <link rel="canonical" href="@yield('canonical', url()->current())">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('og_title', 'Männerkreis Straubing')">
    <meta property="og:description" content="@yield('og_description', 'Authentischer Austausch, Gemeinschaft und persönliches Wachstum für Männer in Niederbayern.')">
    <meta property="og:image" content="@yield('og_image', asset('images/og-image.jpg'))">
    <meta property="og:locale" content="de_DE">
    <meta property="og:site_name" content="Männerkreis Straubing">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="@yield('twitter_title', 'Männerkreis Straubing')">
    <meta property="twitter:description" content="@yield('twitter_description', 'Authentischer Austausch, Gemeinschaft und persönliches Wachstum für Männer in Niederbayern.')">
    <meta property="twitter:image" content="@yield('twitter_image', asset('images/og-image.jpg'))">

    <!-- Security -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Robots -->
    <meta name="robots" content="@yield('robots', 'index, follow')">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant:ital,wght@0,400;0,500;1,400&family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Structured Data -->
    @stack('structured_data')
</head>
<body>
    <!-- Skip Link -->
    <a href="#main" class="skip-link">Zum Inhalt springen</a>

    <!-- Header -->
    <header class="header" id="header">
        <div class="container">
            <div class="header__inner">
                <a href="{{ route('home') }}" class="logo">
                    <span class="logo__symbol">M</span>
                    <span>Männerkreis</span>
                </a>

                <nav class="nav" id="nav">
                    <a href="{{ route('home') }}#ueber" class="nav__link">Über</a>
                    <a href="{{ route('home') }}#reise" class="nav__link">Die Reise</a>
                    <a href="{{ route('home') }}#faq" class="nav__link">Fragen</a>
                    @if($hasNextEvent)
                        <a href="{{ route('event.show') }}" class="nav__cta">Nächster Termin</a>
                    @endif
                </nav>

                <button class="nav-toggle" id="navToggle" aria-label="Menü öffnen">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main id="main">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer__top">
                <div class="footer__brand">
                    <div class="footer__logo">Männerkreis Straubing</div>
                    <p class="footer__text">
                        Ein Raum für echte Begegnung unter Männern.
                        Authentischer Austausch, Gemeinschaft und persönliches Wachstum in Niederbayern.
                    </p>
                </div>

                <div class="footer__nav">
                    <h4 class="footer__heading">Navigation</h4>
                    <ul class="footer__links">
                        <li><a href="{{ route('home') }}#ueber">Über uns</a></li>
                        <li><a href="{{ route('home') }}#reise">Die Reise</a></li>
                        <li><a href="{{ route('home') }}#faq">FAQ</a></li>
                        @if($hasNextEvent)
                            <li><a href="{{ route('event.show') }}">Nächster Termin</a></li>
                        @endif
                    </ul>
                </div>

                <div class="footer__contact">
                    <h4 class="footer__heading">Kontakt</h4>
                    <ul class="footer__links">
                        <li><a href="mailto:hallo@mens-circle.de">E-Mail schreiben</a></li>
                        <li><a href="{{ route('home') }}#newsletter">Newsletter</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer__bottom">
                <p class="footer__copyright">
                    &copy; {{ date('Y') }} Männerkreis Straubing
                </p>
                <div class="footer__legal">
                    <a href="{{ route('impressum') }}">Impressum</a>
                    <a href="{{ route('datenschutz') }}">Datenschutz</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Calendar Modal -->
    <div class="calendar-modal" id="calendarModal">
        <div class="calendar-modal__content">
            <h3>In Kalender speichern</h3>
            <p>Wähle deinen Kalender:</p>
            <div class="calendar-modal__buttons">
                <a href="#" id="calendarGoogle" class="btn btn--secondary" target="_blank" rel="noopener">
                    Google Calendar
                </a>
                <a href="#" id="calendarICS" class="btn btn--secondary" download="maennerkreis-straubing.ics">
                    Apple/Outlook (.ics)
                </a>
            </div>
        </div>
    </div>

    <script>
        window.routes = {
            newsletter: '{{ route('newsletter.subscribe') }}',
            eventRegister: '{{ route('event.register') }}',
            csrfToken: '{{ csrf_token() }}'
        };
    </script>
    @stack('scripts')
</body>
</html>
