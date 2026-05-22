<!DOCTYPE html>
<html lang="de" dir="ltr">
<head>
  @include ('partials.seo-head')
</head>
<body>
  <!-- Skip Link -->
  <a href="#main" class="skip-link">Zum Inhalt springen</a>

  <!-- Header -->
  <header
    class="header"
    id="header"
    x-data="siteHeader"
    :class="{ scrolled: isScrolled, 'header--on-hero': isOnHero }"
  >
    <div class="container">
      <div class="header__inner">
        <a
          href="{{ route('home') }}"
          class="logo"
          aria-label="{{ $settings?->site_name ?? 'Männerkreis' }} - Startseite"
        >
          <x-logo class="logo__icon" />

          <span class="logo__text">Männerkreis</span>
        </a>

        <nav
          class="nav"
          id="nav"
          :class="{ open: isNavOpen }"
          :aria-expanded="isNavOpen"
        >
          @foreach (($headerNavigation?->children ?? []) as $section)
            @php
              $attrs = $section->attributes ?? [];
              $isCta = (bool) ($attrs['is_cta'] ?? false);
              $umamiEvent = $isCta ? 'cta-click' : ($attrs['umami_event'] ?? 'nav-click');
              $umamiTarget = $attrs['umami_event_target'] ?? null;
              $openInNewTab = (bool) ($attrs['open_in_new_tab'] ?? false);
              $linkClass = $isCta ? 'btn btn--primary btn--large nav__cta' : 'nav__link';
            @endphp
            <a
              href="{{ $section->url }}"
              class="{{ $linkClass }}"
              @click="closeNavImmediate()"
              @if ($openInNewTab) target="_blank" rel="noopener noreferrer" @endif
              data-umami-event="{{ $umamiEvent }}"
              @if ($isCta) data-umami-event-location="header" @endif
              @if ($umamiTarget) data-umami-event-target="{{ $umamiTarget }}" @endif
              >{{ $section->title }}</a
            >
          @endforeach
        </nav>

        <button
          class="nav-toggle"
          id="navToggle"
          @click="toggleNav()"
          :class="{ active: isNavOpen }"
          :aria-expanded="isNavOpen"
          :aria-label="isNavOpen ? 'Menü schließen' : 'Menü öffnen'"
          type="button"
        >
          <span></span>
          <span></span>
          <span></span>
        </button>
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
                  >{{ $section->title }}</a
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
                  >{{ $section->title }}</a
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
              >{{ $section->title }}</a
            >
          @endforeach
        </div>
      </div>
    </div>
  </footer>

  <!-- Scroll to Top Button -->
  <button
    class="scroll-to-top"
    id="scrollToTop"
    x-data="scrollToTop"
    x-show="isVisible"
    @click="scrollUp()"
    aria-label="Nach oben scrollen"
    title="Nach oben"
    style="display: none"
  >
    <svg
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      stroke-width="2"
      stroke-linecap="round"
      stroke-linejoin="round"
      aria-hidden="true"
    >
      <polyline points="18 15 12 9 6 15"></polyline>
    </svg>
  </button>

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
