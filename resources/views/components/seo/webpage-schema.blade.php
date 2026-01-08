@props([
    'title',
    'description' => null,
    'url' => null,
    'type' => 'WebPage',
])

@push('structured_data')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "{{ $type }}",
    "name": "{{ $title }}",
    "description": "{{ $description ?? ($settings->site_description ?? 'Authentischer Austausch, Gemeinschaft und persönliches Wachstum für Männer in Niederbayern.') }}",
    "url": "{{ $url ?? url()->current() }}",
    "inLanguage": "de-DE",
    "isPartOf": {
        "@@type": "WebSite",
        "name": "{{ $settings->site_name ?? 'Männerkreis Niederbayern/ Straubing' }}",
        "url": "{{ url('/') }}"
    },
    "publisher": {
        "@@type": "Organization",
        "@@id": "{{ url('/') }}#organization"
    }
}
</script>
@endpush
