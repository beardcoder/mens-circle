@php
    $logoSvg = '<svg fill-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="2" clip-rule="evenodd" viewBox="0 0 396 397" class="h-9 w-9 shrink-0">
      <path fill="currentColor" d="M19.664 171.425s.655-3.856 6.266-3.446c4.634.339 4.265 5.2 4.265 5.2 2.633 39.979 21.063 70.21 21.063 70.21s-36.274-19.308-31.594-71.964M68.22 80.74s-3.914 6.2 2.715 10.79c7.799 5.401 12.497-.26 12.497-.26s22.94-39.58 78.984-54.41c62.286-16.482 128.715 5.265 128.715 5.265S258.531.245 175.288 9.946C102.037 18.483 68.22 80.74 68.22 80.74"/>
      <path fill="currentColor" d="M38.474 97.38q1.968-.257 4.01-.258c17.114 0 31.008 13.895 31.008 31.009s-13.894 31.008-31.008 31.008c-26.309 0-54.461-31.126-18.138-87.76 11.882-18.52 33.8-38.704 52.655-49.5C111.64 2.049 144.285 0 144.285 0S72.508 23.758 38.475 97.379M251.649 350.072s-3.667 1.36-6.117-3.703c-2.023-4.183 2.371-6.294 2.371-6.294 33.306-22.27 50.271-53.345 50.271-53.345s1.417 41.068-46.525 63.342M148.832 353.369s7.327.29 7.988-7.747c.778-9.454-6.474-10.693-6.474-10.693s-45.748-.076-86.614-41.197C18.316 248.032 3.935 179.63 3.935 179.63s-19.97 49.173 30.054 116.413c44.02 59.168 114.843 57.327 114.843 57.327"/>
      <path fill="currentColor" d="M178.118 370.81a31 31 0 0 1-2.227-3.343c-8.558-14.822-3.471-33.802 11.35-42.359s33.801-3.471 42.358 11.35c13.155 22.784.275 62.728-66.934 59.588-21.979-1.03-50.418-9.92-69.196-20.85-34.491-20.082-52.588-47.33-52.588-47.33s56.463 50.281 137.237 42.945M294.606 59.948s3.011 2.495-.149 7.15c-2.61 3.843-6.637 1.092-6.637 1.092-35.938-17.708-71.333-16.863-71.333-16.863s34.858-21.76 78.119 8.62M348.86 147.345s-3.414-6.49-10.704-3.044c-8.576 4.053-6.023 10.952-6.023 10.952s22.808 39.658 7.63 95.608c-16.87 62.183-68.919 108.838-68.919 108.838s52.57-7.292 85.79-84.234c29.232-67.706-7.775-128.12-7.775-128.12"/>
      <path fill="currentColor" d="M349.324 113.259a31 31 0 0 1-1.782 3.6c-8.557 14.823-27.537 19.908-42.358 11.35-14.822-8.556-19.907-27.536-11.35-42.358 13.154-22.784 54.186-31.601 85.071 28.173 10.098 19.55 16.62 48.623 16.541 70.35-.145 39.912-14.694 69.209-14.694 69.209s15.313-74.04-31.428-140.324"/>
    </svg>';
@endphp
<!DOCTYPE html>
<html lang="de" dir="ltr">
<head>
  @include ('partials.seo-head')
</head>
<body class="min-h-screen bg-[var(--bg)] text-[var(--fg)] antialiased">
  <a href="#main" class="skip-link">Zum Inhalt springen</a>

  {{-- Smooth scroll progress bar --}}
  <div
    x-data="scrollProgress"
    class="fixed inset-x-0 top-0 z-[1000] h-0.5 origin-left bg-[var(--accent)]"
    :style="`transform: scaleX(${width})`"
    aria-hidden="true"
  ></div>

  {{-- Header — transparent over hero, frosted on scroll/off-hero --}}
  <header
    x-data="siteHeader"
    :class="{
      'text-[var(--color-parchment)]': onHero && !scrolled,
      'text-[var(--fg)]': !(onHero && !scrolled),
    }"
    class="fixed inset-x-0 top-0 z-[999] transition-colors duration-500"
  >
    <div
      :class="scrolled ? 'opacity-100' : 'opacity-0'"
      class="pointer-events-none absolute inset-0 -z-10 border-b border-[color-mix(in_oklch,var(--border)_45%,transparent)] bg-[color-mix(in_oklch,var(--bg)_55%,transparent)] shadow-[0_10px_24px_-10px_color-mix(in_oklch,var(--color-ink)_8%,transparent)] backdrop-blur-xl backdrop-saturate-150 transition-opacity duration-300"
      aria-hidden="true"
    ></div>

    <div
      :class="scrolled ? 'py-3' : 'py-5'"
      class="container-page flex items-center justify-between gap-4 transition-[padding] duration-300"
    >
      <a
        href="{{ route('home') }}"
        class="relative z-[1001] flex items-center gap-3 font-display text-xl tracking-[-0.02em] transition-opacity hover:opacity-90"
        aria-label="{{ $settings?->site_name ?? 'Männerkreis' }} - Startseite"
      >
        {!! $logoSvg !!}
        <span>Männerkreis</span>
      </a>

      <nav
        :class="navOpen ? 'translate-x-0' : 'translate-x-full md:translate-x-0'"
        class="fixed inset-y-0 right-0 z-50 flex w-full max-w-sm flex-col items-center justify-center gap-8 bg-[var(--bg)] p-8 text-[var(--fg)] transition-transform duration-300 ease-[var(--ease-precise)] md:static md:flex md:w-auto md:max-w-none md:flex-row md:items-center md:gap-8 md:bg-transparent md:p-0 md:text-inherit"
      >
        @foreach ([
            ['#ueber', 'Über'],
            ['#reise', 'Die Reise'],
            ['#faq', 'Fragen'],
        ] as $link)
          <a
            href="{{ route('home') . $link[0] }}"
            class="text-[0.78rem] font-semibold uppercase tracking-[0.18em] transition-colors hover:text-[var(--accent)]"
            data-umami-event="nav-click"
            data-umami-event-target="{{ ltrim($link[0], '#') }}"
            @click="onLinkClick"
            >{{ $link[1] }}</a
          >
        @endforeach
        <a
          href="{{ route('breathing.show') }}"
          class="text-[0.78rem] font-semibold uppercase tracking-[0.18em] transition-colors hover:text-[var(--accent)]"
          data-umami-event="nav-click"
          data-umami-event-target="atemuebung"
          @click="onLinkClick"
          >Atemübung</a
        >
        @if ($hasNextEvent)
          <a
            href="{{ $nextEventUrl }}"
            class="btn btn-primary"
            data-umami-event="cta-click"
            data-umami-event-location="header"
            data-umami-event-action="go-to-event"
            @click="onLinkClick"
            >Nächster Termin</a
          >
        @endif

        <button
          type="button"
          @click="toggleNav"
          class="absolute top-5 right-5 grid h-10 w-10 place-items-center rounded-full border border-[var(--border)] md:hidden"
          aria-label="Menü schließen"
          x-show="navOpen"
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5">
            <line x1="18" y1="6" x2="6" y2="18" />
            <line x1="6" y1="6" x2="18" y2="18" />
          </svg>
        </button>
      </nav>

      <button
        type="button"
        @click="toggleNav"
        x-show="!navOpen"
        class="relative z-[1001] grid h-10 w-10 place-items-center rounded-full border border-current/30 md:hidden"
        aria-label="Menü öffnen"
      >
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5" aria-hidden="true">
          <line x1="3" y1="6" x2="21" y2="6" />
          <line x1="3" y1="12" x2="21" y2="12" />
          <line x1="3" y1="18" x2="21" y2="18" />
        </svg>
      </button>
    </div>
  </header>

  <main id="main" class="has-[[data-hero]:first-child]:pt-0 pt-20">
    @yield ('content')
  </main>

  <footer class="bg-[var(--bg-deep)] text-[var(--color-parchment)] py-16">
    <div class="container-page">
      <div class="grid gap-12 md:grid-cols-3">
        <div>
          <a
            href="{{ route('home') }}"
            class="flex items-center gap-3 font-display text-xl font-semibold mb-4"
          >
            {!! $logoSvg !!}
            <span>{{ $settings?->site_name ?? 'Männerkreis' }}</span>
          </a>
          <p class="text-sm text-[var(--color-sand)] leading-relaxed mb-6">
            {{ $settings?->site_description ?: 'Ein Raum für echte Begegnung unter Männern. Authentischer Austausch, Gemeinschaft und persönliches Wachstum in Niederbayern.' }}
          </p>
          @if (!empty($socialLinks))
            <ul class="flex flex-wrap gap-3">
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

        <div>
          <h3 class="font-display text-lg mb-4">Navigation</h3>
          <ul class="space-y-2 text-sm text-[var(--color-sand)]">
            <li>
              <a
                href="{{ route('home') }}#ueber"
                class="hover:text-[var(--color-terracotta-light)]"
                data-umami-event="footer-link"
                data-umami-event-target="ueber"
                >Über uns</a
              >
            </li>
            <li>
              <a
                href="{{ route('home') }}#reise"
                class="hover:text-[var(--color-terracotta-light)]"
                data-umami-event="footer-link"
                data-umami-event-target="reise"
                >Die Reise</a
              >
            </li>
            <li>
              <a
                href="{{ route('home') }}#faq"
                class="hover:text-[var(--color-terracotta-light)]"
                data-umami-event="footer-link"
                data-umami-event-target="faq"
                >FAQ</a
              >
            </li>
            <li>
              <a
                href="{{ route('breathing.show') }}"
                class="hover:text-[var(--color-terracotta-light)]"
                data-umami-event="footer-link"
                data-umami-event-target="atemuebung"
                >Atemübung</a
              >
            </li>
            @if ($hasNextEvent)
              <li>
                <a
                  href="{{ $nextEventUrl }}"
                  class="hover:text-[var(--color-terracotta-light)]"
                  data-umami-event="footer-link"
                  data-umami-event-target="event"
                  >Nächster Termin</a
                >
              </li>
            @endif
          </ul>
        </div>

        <div>
          <h3 class="font-display text-lg mb-4">Kontakt</h3>
          <ul class="space-y-2 text-sm text-[var(--color-sand)]">
            @if ($settings?->contact_email)
              <li>
                <a
                  href="mailto:{{ $settings->contact_email }}"
                  class="hover:text-[var(--color-terracotta-light)]"
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
                  class="hover:text-[var(--color-terracotta-light)]"
                  data-umami-event="contact-click"
                  data-umami-event-type="phone"
                  >{{ $settings->contact_phone }}</a
                >
              </li>
            @endif
            <li>
              <a
                href="{{ route('home') }}#newsletter"
                class="hover:text-[var(--color-terracotta-light)]"
                data-umami-event="footer-link"
                data-umami-event-target="newsletter"
                >Newsletter</a
              >
            </li>
          </ul>
        </div>
      </div>

      <div
        class="mt-12 flex flex-col items-start justify-between gap-4 border-t border-white/10 pt-6 text-sm text-[var(--color-sand)] md:flex-row md:items-center"
      >
        <p>{{ $settings?->footer_text ?? '© 2024 Männerkreis Niederbayern' }}</p>
        <div class="flex gap-6">
          <a
            href="{{ route('page.show', 'impressum') }}"
            class="hover:text-[var(--color-terracotta-light)]"
            >Impressum</a
          >
          <a
            href="{{ route('page.show', 'datenschutz') }}"
            class="hover:text-[var(--color-terracotta-light)]"
            >Datenschutz</a
          >
        </div>
      </div>
    </div>
  </footer>

  {{-- Scroll-to-top --}}
  <button
    x-data="scrollToTop"
    x-show="visible"
    x-transition.opacity
    @click="go"
    class="fixed bottom-6 right-6 z-[100] grid h-12 w-12 place-items-center rounded-full bg-[var(--accent)] text-white shadow-lg transition-transform hover:-translate-y-0.5"
    aria-label="Nach oben scrollen"
  >
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5" aria-hidden="true">
      <polyline points="18 15 12 9 6 15"></polyline>
    </svg>
  </button>

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
