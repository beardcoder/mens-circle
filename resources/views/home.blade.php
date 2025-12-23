@extends('layouts.app')

@section('title', $page->title ?? 'Männerkreis Straubing')

@push('structured_data')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "Organization",
    "name": "Männerkreis Straubing",
    "url": "{{ url('/') }}",
    "logo": "{{ asset('images/logo.png') }}",
    "description": "Authentischer Austausch, Gemeinschaft und persönliches Wachstum für Männer in Niederbayern.",
    "address": {
        "@@type": "PostalAddress",
        "addressLocality": "Straubing",
        "addressRegion": "Bayern",
        "addressCountry": "DE"
    },
    "email": "hallo@mens-circle.de",
    "areaServed": {
        "@@type": "Place",
        "name": "Niederbayern"
    },
    "sameAs": []
}
</script>
@endpush

@section('content')
    @if($page->content_blocks && is_array($page->content_blocks))
        @foreach($page->content_blocks as $block)
            @if($block['type'] === 'hero')
                <x-blocks.hero :block="$block" />
            @elseif($block['type'] === 'intro')
                <x-blocks.intro :block="$block" />
            @elseif($block['type'] === 'text_section')
                <x-blocks.text-section :block="$block" />
            @elseif($block['type'] === 'value_items')
                <x-blocks.value-items :block="$block" />
            @elseif($block['type'] === 'moderator')
                <x-blocks.moderator :block="$block" />
            @elseif($block['type'] === 'journey_steps')
                <x-blocks.journey-steps :block="$block" />
            @elseif($block['type'] === 'faq')
                <x-blocks.faq :block="$block" />
            @elseif($block['type'] === 'newsletter')
                <x-blocks.newsletter :block="$block" />
            @elseif($block['type'] === 'cta')
                <x-blocks.cta :block="$block" />
            @endif
        @endforeach
    @endif
@endsection
