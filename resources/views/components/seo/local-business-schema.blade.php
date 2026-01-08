@push('structured_data')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "LocalBusiness",
    "@@id": "{{ url('/') }}#organization",
    "name": "{{ $settings->site_name ?? 'Männerkreis Niederbayern/ Straubing' }}",
    "description": "{{ $settings->site_description ?? 'Authentischer Austausch, Gemeinschaft und persönliches Wachstum für Männer in Niederbayern.' }}",
    "url": "{{ url('/') }}",
    "logo": {
        "@@type": "ImageObject",
        "url": "{{ asset('images/logo-color.png') }}",
        "width": 512,
        "height": 512
    },
    "image": "{{ asset('images/logo-color.png') }}",
    "email": "{{ $settings->contact_email ?? 'hallo@@mens-circle.de' }}",
    @if($settings->contact_phone ?? false)
    "telephone": "{{ $settings->contact_phone }}",
    @endif
    "address": {
        "@@type": "PostalAddress",
        "addressLocality": "Straubing",
        "addressRegion": "Bayern",
        "postalCode": "94315",
        "addressCountry": "DE"
    },
    "geo": {
        "@@type": "GeoCoordinates",
        "latitude": 48.8777,
        "longitude": 12.5731
    },
    "areaServed": {
        "@@type": "GeoCircle",
        "geoMidpoint": {
            "@@type": "GeoCoordinates",
            "latitude": 48.8777,
            "longitude": 12.5731
        },
        "geoRadius": "50000"
    },
    "priceRange": "€",
    "openingHoursSpecification": {
        "@@type": "OpeningHoursSpecification",
        "dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"],
        "opens": "09:00",
        "closes": "18:00"
    },
    "sameAs": [
        @if(!empty($socialLinks))
            @foreach($socialLinks as $link)
                "{{ $link['value'] }}"@if(!$loop->last),@endif
            @endforeach
        @endif
    ]
}
</script>
@endpush
