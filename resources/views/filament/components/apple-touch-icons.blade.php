@php
    $faviconVersion = substr(md5_file(public_path('favicon.svg')), 0, 8);
@endphp

<link rel="apple-touch-icon" sizes="57x57" href="{{ asset('favicon-57x57.png') }}?v={{ $faviconVersion }}">
<link rel="apple-touch-icon" sizes="60x60" href="{{ asset('favicon-60x60.png') }}?v={{ $faviconVersion }}">
<link rel="apple-touch-icon" sizes="72x72" href="{{ asset('favicon-72x72.png') }}?v={{ $faviconVersion }}">
<link rel="apple-touch-icon" sizes="76x76" href="{{ asset('favicon-76x76.png') }}?v={{ $faviconVersion }}">
<link rel="apple-touch-icon" sizes="114x114" href="{{ asset('favicon-114x114.png') }}?v={{ $faviconVersion }}">
<link rel="apple-touch-icon" sizes="120x120" href="{{ asset('favicon-120x120.png') }}?v={{ $faviconVersion }}">
<link rel="apple-touch-icon" sizes="144x144" href="{{ asset('favicon-144x144.png') }}?v={{ $faviconVersion }}">
<link rel="apple-touch-icon" sizes="152x152" href="{{ asset('favicon-152x152.png') }}?v={{ $faviconVersion }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon-180x180.png') }}?v={{ $faviconVersion }}">
<link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v={{ $faviconVersion }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}?v={{ $faviconVersion }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v={{ $faviconVersion }}">
<link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicon-96x96.png') }}?v={{ $faviconVersion }}">
<link rel="icon" type="image/png" sizes="192x192" href="{{ asset('favicon-192x192.png') }}?v={{ $faviconVersion }}">
<link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v={{ $faviconVersion }}">
<link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v={{ $faviconVersion }}">
<link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v={{ $faviconVersion }}" />
<meta name="msapplication-TileColor" content="#000000">
<meta name="msapplication-TileImage" content="{{ asset('favicon-144x144.png')  }}">
<meta name="msapplication-config" content="{{ asset('browserconfig.xml') }}?v={{ $faviconVersion }}">
<link rel="manifest" href="{{ asset('manifest.json') }}?v={{ $faviconVersion }}">
<meta name="theme-color" content="#000000">
