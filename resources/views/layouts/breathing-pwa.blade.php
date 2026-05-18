<!DOCTYPE html>
<html lang="de" dir="ltr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />

  <title>@yield('title', 'Atemübung – Männerkreis')</title>
  <meta name="description" content="@yield('meta_description', 'Geführte Wim-Hof-Atemübung für Klarheit, Energie und innere Ruhe.')" />

  <!-- PWA / Installability -->
  <meta name="mobile-web-app-capable" content="yes" />
  <meta name="apple-mobile-web-app-capable" content="yes" />
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
  <meta name="apple-mobile-web-app-title" content="Atemübung" />
  <meta name="application-name" content="Atemübung" />
  <meta name="theme-color" content="#2c1a0c" />
  <meta name="msapplication-TileColor" content="#2c1a0c" />

  <!-- Manifest (breathing-app specific) -->
  @php
    $manifestVersion = once(static fn (): string => file_exists(public_path('breathing-manifest.json'))
        ? substr(md5_file(public_path('breathing-manifest.json')), 0, 8)
        : '1');
  @endphp
  <link rel="manifest" href="{{ asset('breathing-manifest.json') }}?v={{ $manifestVersion }}" />

  <!-- Favicon -->
  @php
    $faviconVersion = once(static fn (): string => substr(md5_file(public_path('favicon.svg')), 0, 8));
  @endphp
  <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v={{ $faviconVersion }}" />
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon-180x180.png') }}?v={{ $faviconVersion }}" />

  <!-- Preload critical fonts -->
  @php
    $manifest = once(static function (): array {
        $path = public_path('build/manifest.json');
        if (!file_exists($path)) {
            return [];
        }
        return json_decode(file_get_contents($path), true) ?: [];
    });
    $dmSansPath = $manifest['node_modules/@fontsource-variable/dm-sans/files/dm-sans-latin-wght-normal.woff2']['file'] ?? null;
    $playfairPath = $manifest['node_modules/@fontsource-variable/playfair-display/files/playfair-display-latin-wght-normal.woff2']['file'] ?? null;
  @endphp
  @if ($playfairPath)
    <link rel="preload" href="{{ asset('build/' . $playfairPath) }}" as="font" type="font/woff2" crossorigin fetchpriority="high" />
  @endif
  @if ($dmSansPath)
    <link rel="preload" href="{{ asset('build/' . $dmSansPath) }}" as="font" type="font/woff2" crossorigin fetchpriority="high" />
  @endif

  <!-- Styles -->
  @vite (['resources/css/app.css'])

  <!-- Analytics -->
  @include ('components.analytics.umami')
</head>
<body class="breathing-pwa-layout">

  <header class="breathing-pwa-header">
    <a href="{{ route('home') }}" class="breathing-pwa-header__title" aria-label="Männerkreis – Startseite">
      <svg class="breathing-pwa-header__logo" fill-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="2" clip-rule="evenodd" viewBox="0 0 396 397" aria-hidden="true">
        <path fill="currentColor" d="M19.664 171.425s.655-3.856 6.266-3.446c4.634.339 4.265 5.2 4.265 5.2 2.633 39.979 21.063 70.21 21.063 70.21s-36.274-19.308-31.594-71.964M68.22 80.74s-3.914 6.2 2.715 10.79c7.799 5.401 12.497-.26 12.497-.26s22.94-39.58 78.984-54.41c62.286-16.482 128.715 5.265 128.715 5.265S258.531.245 175.288 9.946C102.037 18.483 68.22 80.74 68.22 80.74" />
        <path fill="currentColor" d="M38.474 97.38q1.968-.257 4.01-.258c17.114 0 31.008 13.895 31.008 31.009s-13.894 31.008-31.008 31.008c-26.309 0-54.461-31.126-18.138-87.76 11.882-18.52 33.8-38.704 52.655-49.5C111.64 2.049 144.285 0 144.285 0S72.508 23.758 38.475 97.379M251.649 350.072s-3.667 1.36-6.117-3.703c-2.023-4.183 2.371-6.294 2.371-6.294 33.306-22.27 50.271-53.345 50.271-53.345s1.417 41.068-46.525 63.342M148.832 353.369s7.327.29 7.988-7.747c.778-9.454-6.474-10.693-6.474-10.693s-45.748-.076-86.614-41.197C18.316 248.032 3.935 179.63 3.935 179.63s-19.97 49.173 30.054 116.413c44.02 59.168 114.843 57.327 114.843 57.327" />
        <path fill="currentColor" d="M178.118 370.81a31 31 0 0 1-2.227-3.343c-8.558-14.822-3.471-33.802 11.35-42.359s33.801-3.471 42.358 11.35c13.155 22.784.275 62.728-66.934 59.588-21.979-1.03-50.418-9.92-69.196-20.85-34.491-20.082-52.588-47.33-52.588-47.33s56.463 50.281 137.237 42.945M294.606 59.948s3.011 2.495-.149 7.15c-2.61 3.843-6.637 1.092-6.637 1.092-35.938-17.708-71.333-16.863-71.333-16.863s34.858-21.76 78.119 8.62M348.86 147.345s-3.414-6.49-10.704-3.044c-8.576 4.053-6.023 10.952-6.023 10.952s22.808 39.658 7.63 95.608c-16.87 62.183-68.919 108.838-68.919 108.838s52.57-7.292 85.79-84.234c29.232-67.706-7.775-128.12-7.775-128.12" />
        <path fill="currentColor" d="M349.324 113.259a31 31 0 0 1-1.782 3.6c-8.557 14.823-27.537 19.908-42.358 11.35-14.822-8.556-19.907-27.536-11.35-42.358 13.154-22.784 54.186-31.601 85.071 28.173 10.098 19.55 16.62 48.623 16.541 70.35-.145 39.912-14.694 69.209-14.694 69.209s15.313-74.04-31.428-140.324" />
      </svg>
      <span>Atemübung</span>
    </a>

    <button
      type="button"
      class="breathing-pwa-header__install"
      id="pwaInstallBtn"
      hidden
      aria-label="App installieren"
    >
      <svg class="breathing-hero__app-link-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
      </svg>
      Installieren
    </button>
  </header>

  <main id="main" class="breathing-pwa-main">
    @yield ('content')
  </main>

  <!-- JavaScript -->
  <script>
    window.routes = {
      newsletter: '{{ route('newsletter.subscribe') }}',
      eventRegister: '{{ route('event.register') }}',
    };
  </script>

  @vite (['resources/js/app.ts'])

  <!-- PWA: service worker + install prompt -->
  <script>
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', function () {
        navigator.serviceWorker
          .register('/breathing-sw.js', { scope: '/atemuebung/' })
          .catch(function (err) {
            console.warn('Service worker registration failed:', err);
          });
      });
    }

    // Show install button when the browser fires beforeinstallprompt
    let deferredPrompt = null;
    const installBtn = document.getElementById('pwaInstallBtn');

    window.addEventListener('beforeinstallprompt', function (e) {
      e.preventDefault();
      deferredPrompt = e;
      if (installBtn) installBtn.hidden = false;
    });

    if (installBtn) {
      installBtn.addEventListener('click', function () {
        if (!deferredPrompt) return;
        deferredPrompt.prompt();
        deferredPrompt.userChoice.then(function () {
          deferredPrompt = null;
          installBtn.hidden = true;
        });
      });
    }

    window.addEventListener('appinstalled', function () {
      deferredPrompt = null;
      if (installBtn) installBtn.hidden = true;
    });
  </script>

  @stack ('scripts')
</body>
</html>
