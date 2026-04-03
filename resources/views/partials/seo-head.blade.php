<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<!-- Primary Meta Tags -->
<title>
  @yield ('title', $settings?->site_name . ($settings?->site_tagline ? ' – ' . $settings?->site_tagline : ''))
</title>
<meta
  name="title"
  content="@yield('meta_title', $settings?->site_name . ($settings?->site_tagline ? ' – ' . $settings?->site_tagline : ''))"
/>
<meta
  name="description"
  content="@yield('meta_description', $settings?->site_description ?: 'Authentischer Austausch, Gemeinschaft und persönliches Wachstum für Männer in Niederbayern.')"
/>
<meta
  name="keywords"
  content="@yield('meta_keywords', 'Männerkreis, Niederbayern, Männergruppe, persönliches Wachstum, Gemeinschaft, Männer')"
/>
<meta name="author" content="Markus Sommer" />
<link rel="canonical" href="@yield('canonical', url()->current())" />

<!-- Language & Locale -->
<link
  rel="alternate"
  hreflang="de"
  href="@yield('canonical', url()->current())"
/>
<link
  rel="alternate"
  hreflang="x-default"
  href="@yield('canonical', url()->current())"
/>

<!-- Theme Color -->
<meta name="theme-color" content="#3d2817" />
<meta name="color-scheme" content="light" />
<meta name="msapplication-TileColor" content="#3d2817" />

<!-- Open Graph / Facebook -->
<meta property="og:type" content="@yield('og_type', 'website')" />
<meta property="og:url" content="{{ url()->current() }}" />
<meta property="og:title" content="@yield('og_title', $settings?->site_name)" />
<meta
  property="og:description"
  content="@yield('og_description', $settings?->site_description ?: 'Authentischer Austausch, Gemeinschaft und persönliches Wachstum für Männer in Niederbayern.')"
/>
<meta
  property="og:image"
  content="@yield('og_image', asset('images/logo-color.png'))"
/>
<meta property="og:image:width" content="1200" />
<meta property="og:image:height" content="630" />
<meta
  property="og:image:alt"
  content="@yield('og_image_alt', 'Männerkreis Niederbayern/ Straubing - Gemeinschaft für Männer')"
/>
<meta property="og:locale" content="de_DE" />
<meta property="og:site_name" content="{{ $settings?->site_name }}" />

<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:url" content="{{ url()->current() }}" />
<meta
  name="twitter:title"
  content="@yield('twitter_title', $settings?->site_name)"
/>
<meta
  name="twitter:description"
  content="@yield('twitter_description', $settings?->site_description ?: 'Authentischer Austausch, Gemeinschaft und persönliches Wachstum für Männer in Niederbayern.')"
/>
<meta
  name="twitter:image"
  content="@yield('twitter_image', asset('images/logo-color.png'))"
/>
<meta
  name="twitter:image:alt"
  content="@yield('twitter_image_alt', 'Männerkreis Niederbayern/ Straubing')"
/>

<!-- Security -->
<meta name="csrf-token" content="{{ csrf_token() }}" />

<!-- Robots -->
<meta name="robots" content="@yield('robots', 'index, follow')" />
<meta name="googlebot" content="@yield('robots', 'index, follow')" />

<!-- Preload critical fonts (latin subsets only for German site) -->
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
@if ($dmSansPath)
  <link rel="preload" href="{{ asset('build/' . $dmSansPath) }}" as="font" type="font/woff2" crossorigin />
@endif
@if ($playfairPath)
  <link rel="preload" href="{{ asset('build/' . $playfairPath) }}" as="font" type="font/woff2" crossorigin />
@endif

<!-- Favicon -->
@php
    $faviconVersion = once(static fn (): string => substr(md5_file(public_path('favicon.svg')), 0, 8));
@endphp
<link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v={{ $faviconVersion }}" />
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v={{ $faviconVersion }}" />
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}?v={{ $faviconVersion }}" />
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon-180x180.png') }}?v={{ $faviconVersion }}" />
<link rel="manifest" href="{{ asset('manifest.json') }}?v={{ $faviconVersion }}" />

<!-- Styles -->
@vite (['resources/css/app.css'])

<!-- Analytics -->
@include ('components.analytics.umami')

<!-- Structured Data -->
@if ($localBusinessSchema ?? null)
    {!! $localBusinessSchema->toScript() !!}
@endif
@if ($organizationSchema ?? null)
    {!! $organizationSchema->toScript() !!}
@endif
@if ($websiteSchema ?? null)
    {!! $websiteSchema->toScript() !!}
@endif
@stack ('structured_data')
