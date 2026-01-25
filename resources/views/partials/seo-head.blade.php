<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- Primary Meta Tags -->
<title>@yield('title', $settings?->site_name . ($settings?->site_tagline ? ' – ' . $settings?->site_tagline : ''))</title>
<meta name="title" content="@yield('meta_title', $settings?->site_name . ($settings?->site_tagline ? ' – ' . $settings?->site_tagline : ''))">
<meta name="description" content="@yield('meta_description', $settings?->site_description ?: 'Authentischer Austausch, Gemeinschaft und persönliches Wachstum für Männer in Niederbayern.')">
<meta name="keywords" content="@yield('meta_keywords', 'Männerkreis, Niederbayern, Männergruppe, persönliches Wachstum, Gemeinschaft, Männer')">
<meta name="author" content="Markus Sommer">
<link rel="canonical" href="@yield('canonical', url()->current())">

<!-- Language & Locale -->
<link rel="alternate" hreflang="de" href="@yield('canonical', url()->current())">
<link rel="alternate" hreflang="x-default" href="@yield('canonical', url()->current())">

<!-- Theme Color -->
<meta name="theme-color" content="#3d2817">
<meta name="color-scheme" content="light">
<meta name="msapplication-TileColor" content="#3d2817">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="@yield('og_type', 'website')">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:title" content="@yield('og_title', $settings?->site_name)">
<meta property="og:description" content="@yield('og_description', $settings?->site_description ?: 'Authentischer Austausch, Gemeinschaft und persönliches Wachstum für Männer in Niederbayern.')">
<meta property="og:image" content="@yield('og_image', asset('images/logo-color.png'))">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="@yield('og_image_alt', 'Männerkreis Niederbayern/ Straubing - Gemeinschaft für Männer')">
<meta property="og:locale" content="de_DE">
<meta property="og:site_name" content="{{ $settings?->site_name }}">

<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:url" content="{{ url()->current() }}">
<meta name="twitter:title" content="@yield('twitter_title', $settings?->site_name)">
<meta name="twitter:description" content="@yield('twitter_description', $settings?->site_description ?: 'Authentischer Austausch, Gemeinschaft und persönliches Wachstum für Männer in Niederbayern.')">
<meta name="twitter:image" content="@yield('twitter_image', asset('images/logo-color.png'))">
<meta name="twitter:image:alt" content="@yield('twitter_image_alt', 'Männerkreis Niederbayern/ Straubing')">

<!-- Security -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Robots -->
<meta name="robots" content="@yield('robots', 'index, follow')">
<meta name="googlebot" content="@yield('robots', 'index, follow')">

<!-- Favicon -->
<link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.ico') }}">
<link rel="apple-touch-icon" href="{{ asset('logo.png') }}">

<!-- Preload Critical Fonts for LCP -->
<link rel="preload" as="font" type="font/woff2" href="/build/assets/dm-sans-latin-wght-normal-Xz1IZZA0.woff2" crossorigin>
<link rel="preload" as="font" type="font/woff2" href="/build/assets/playfair-display-latin-wght-normal-BOwq7MWX.woff2" crossorigin>

<!-- Styles -->
@vite(['resources/css/app.css'])

<!-- Analytics -->
@include('components.analytics.umami')

<!-- Structured Data -->
@stack('structured_data')
