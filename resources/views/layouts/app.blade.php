<!DOCTYPE html>
<html lang="de" dir="ltr">
<head>
  {{-- Flag JS synchronously so reveals only hide their elements when
       JavaScript is available — without JS, all [data-reveal] content stays
       visible. Render-blocking + ahead of <body> = no flash of hidden content. --}}
  <script>
    document.documentElement.classList.add('motion-ready');
  </script>
  @include ('partials.seo-head')
</head>
<body>
  {{-- Top sentinel: a static, in-flow target at position 0 so the
       scroll-to-top link reliably scrolls to the top. (The header is
       position:fixed, so an anchor to it never scrolls.) --}}
  <span id="top" aria-hidden="true"></span>

  <x-sprite-defs />

  {{-- Editorial side rails — decorative magazine framing, hidden on small screens --}}
  <aside class="side-rail side-rail--left" aria-hidden="true">
    <span class="side-rail__text">Männerkreis · Niederbayern</span>
  </aside>
  <aside class="side-rail side-rail--right" aria-hidden="true">
    <span class="side-rail__text">Straubing · Bayern</span>
  </aside>

  <!-- Skip Link -->
  <a href="#main" class="skip-link">Zum Inhalt springen</a>

  <!-- Header -->
  @php
    $headerItems = collect($headerNavigation?->children ?? []);
    $headerNavLinks = $headerItems->reject(fn ($section) => (bool) ($section->attributes['is_cta'] ?? false))->values();
    $headerCtaLinks = $headerItems->filter(fn ($section) => (bool) ($section->attributes['is_cta'] ?? false))->values();
  @endphp
  <header class="header" id="header" data-lume="site-header">
    <div class="container">
      <div class="header__inner">
        <a
          href="{{ route('home') }}"
          class="header__logo logo"
          aria-label="{{ $settings?->site_name ?? 'Männerkreis' }} - Startseite"
        >
          <x-logo class="logo__icon" />

          <span class="logo__text">Männerkreis</span>
        </a>

        <nav
          class="nav"
          id="nav"
          data-lume-part="nav"
          aria-label="Hauptnavigation"
        >
          {{-- Ambient "circle" — concentric rings that breathe behind the
               menu (mobile only). Decorative, so hidden from assistive tech. --}}
          <span class="nav__ambient" aria-hidden="true">
            <span class="nav__ring"></span>
            <span class="nav__ring"></span>
            <span class="nav__ring"></span>
          </span>

          <ul class="nav__list">
            @foreach ($headerNavLinks as $section)
              @php
                $attrs = $section->attributes ?? [];
                $umamiEvent = $attrs['umami_event'] ?? 'nav-click';
                $umamiTarget = $attrs['umami_event_target'] ?? null;
                $openInNewTab = (bool) ($attrs['open_in_new_tab'] ?? false);
              @endphp
              <li class="nav__item">
                <a
                  href="{{ $section->url }}"
                  class="nav__link"
                  data-lume-part="nav-link"
                  @if ($openInNewTab) target="_blank" rel="noopener noreferrer" @endif
                  data-umami-event="{{ $umamiEvent }}"
                  @if ($umamiTarget) data-umami-event-target="{{ $umamiTarget }}" @endif
                >
                  <span
                    class="nav__index"
                    aria-hidden="true"
                    >{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span
                  >
                  <span class="nav__label">{{ $section->title }}</span>
                </a>
              </li>
            @endforeach
          </ul>

          <p class="nav__meta">Atme durch. Du bist angekommen.</p>
        </nav>

        <div class="header__actions">
          @foreach ($headerCtaLinks as $section)
            @php
              $attrs = $section->attributes ?? [];
              $umamiTarget = $attrs['umami_event_target'] ?? null;
              $openInNewTab = (bool) ($attrs['open_in_new_tab'] ?? false);
            @endphp
            <a
              href="{{ $section->url }}"
              class="btn btn--primary nav__cta"
              data-lume-part="nav-link"
              @if ($openInNewTab) target="_blank" rel="noopener noreferrer" @endif
              data-umami-event="cta-click"
              data-umami-event-location="header"
              @if ($umamiTarget) data-umami-event-target="{{ $umamiTarget }}" @endif
            >
              {{ $section->title }}</a
            >
          @endforeach

          <button
            class="nav-toggle"
            id="navToggle"
            type="button"
            data-lume-part="toggle"
            aria-controls="nav"
            aria-expanded="false"
            aria-label="Menü öffnen"
          >
            <span class="nav-toggle__bars" aria-hidden="true">
              <span class="nav-toggle__bar"></span>
              <span class="nav-toggle__bar"></span>
              <span class="nav-toggle__bar"></span>
            </span>
          </button>
        </div>
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <main id="main">
    @yield ('content')
  </main>

  <!-- Footer -->
  <footer class="footer">
    <div class="container">
      <div class="footer__top">
        <div class="footer__brand">
          <a href="{{ route('home') }}" class="footer__logo">
            <x-logo class="footer__logo-icon" />

            <span>{{ $settings?->site_name ?? 'Männerkreis' }}</span>
          </a>
          <p class="footer__text">
            {{ $settings?->site_description ?: 'Ein Raum für echte Begegnung unter Männern. Authentischer Austausch, Gemeinschaft und persönliches Wachstum in Niederbayern.' }}
          </p>
          @if (!empty($socialLinks))
            <ul class="footer__social-links">
              @foreach ($socialLinks as $link)
                <li>
                  <x-social-icon
                    variant="link"
                    :type="$link['type'] ?? null"
                    :url="$link['value']"
                    :label="$link['label'] ?? ''"
                  />
                </li>
              @endforeach
            </ul>
          @endif
        </div>

        <div class="footer__nav">
          <h3 class="footer__heading">Navigation</h3>
          <ul class="footer__links">
            @foreach (($footerPrimaryNavigation?->children ?? []) as $section)
              @php
                $attrs = $section->attributes ?? [];
                $umamiTarget = $attrs['umami_event_target'] ?? null;
                $openInNewTab = (bool) ($attrs['open_in_new_tab'] ?? false);
              @endphp
              <li>
                <a
                  href="{{ $section->url }}"
                  @if ($openInNewTab) target="_blank" rel="noopener noreferrer" @endif
                  data-umami-event="footer-link"
                  @if ($umamiTarget) data-umami-event-target="{{ $umamiTarget }}" @endif
                >
                  {{ $section->title }}</a
                >
              </li>
            @endforeach
          </ul>
        </div>

        <div class="footer__contact">
          <h3 class="footer__heading">Kontakt</h3>
          <ul class="footer__links">
            @if ($settings?->contact_email)
              <li>
                <a
                  href="mailto:{{ $settings->contact_email }}"
                  data-umami-event="contact-click"
                  data-umami-event-type="email"
                  >E-Mail schreiben</a
                >
              </li>
            @endif
            @if ($settings?->contact_phone)
              <li>
                <a
                  href="tel:{{ str_replace([' ', '-', '(', ')'], '', $settings->contact_phone) }}"
                  data-umami-event="contact-click"
                  data-umami-event-type="phone"
                  >{{ $settings->contact_phone }}</a
                >
              </li>
            @endif
            @foreach (($footerContactNavigation?->children ?? []) as $section)
              @php
                $attrs = $section->attributes ?? [];
                $umamiTarget = $attrs['umami_event_target'] ?? null;
                $openInNewTab = (bool) ($attrs['open_in_new_tab'] ?? false);
              @endphp
              <li>
                <a
                  href="{{ $section->url }}"
                  @if ($openInNewTab) target="_blank" rel="noopener noreferrer" @endif
                  data-umami-event="footer-link"
                  @if ($umamiTarget) data-umami-event-target="{{ $umamiTarget }}" @endif
                >
                  {{ $section->title }}</a
                >
              </li>
            @endforeach
          </ul>
        </div>
      </div>

      <div class="footer__bottom">
        <p class="footer__copyright">
          {{ $settings?->footer_text ?? '© 2024 Männerkreis Niederbayern' }}
        </p>
        <div class="footer__legal">
          @foreach (($footerLegalNavigation?->children ?? []) as $section)
            @php
              $attrs = $section->attributes ?? [];
              $umamiTarget = $attrs['umami_event_target'] ?? null;
              $openInNewTab = (bool) ($attrs['open_in_new_tab'] ?? false);
            @endphp
            <a
              href="{{ $section->url }}"
              @if ($openInNewTab) target="_blank" rel="noopener noreferrer" @endif
              data-umami-event="footer-link"
              @if ($umamiTarget) data-umami-event-target="{{ $umamiTarget }}" @endif
            >
              {{ $section->title }}</a
            >
          @endforeach
        </div>
      </div>
    </div>
  </footer>

  <!-- Scroll to Top — CSS-only via scroll-driven animation timeline -->
  <a
    href="#top"
    class="scroll-to-top"
    aria-label="Nach oben scrollen"
    title="Nach oben"
  >
    <x-sprite name="chevron-up" />
  </a>

  <!-- JavaScript -->
  <script>
    window.routes = {
      newsletter: '{{ route('newsletter.subscribe') }}',
      eventRegister: '{{ route('event.register') }}',
    };
  </script>

  @vite (['resources/js/app.ts'])
  @stack ('scripts')
</body>
</html>
