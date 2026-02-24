@extends('layouts.app')

@section('title', $page->title ?? 'Männerkreis Niederbayern/ Straubing')
@section('meta_title', $page->meta['meta_title'] ?? $page->title ?? 'Männerkreis Niederbayern/ Straubing')
@section('meta_description', $page->meta['meta_description'] ?? 'Authentischer Austausch, Gemeinschaft und persönliches Wachstum für Männer in Niederbayern.')
@section('meta_keywords', $page->meta['meta_keywords'] ?? 'Männerkreis, Niederbayern, Männergruppe, persönliches Wachstum, Gemeinschaft, Männer')

@section('og_title', $page->meta['og_title'] ?? $page->title ?? 'Männerkreis Niederbayern/ Straubing')
@section('og_description', $page->meta['og_description'] ?? $page->meta['meta_description'] ?? 'Authentischer Austausch, Gemeinschaft und persönliches Wachstum für Männer in Niederbayern.')
@if(!empty($page->meta['og_image']))
    @section('og_image', asset('storage/' . $page->meta['og_image']))
@endif

<x-seo.local-business-schema />

@push('structured_data')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "Organization",
    "@@id": "{{ url('/') }}#organization",
    "name": "Männerkreis Niederbayern/ Straubing",
    "url": "{{ url('/') }}",
    "logo": {
        "@@type": "ImageObject",
        "url": "{{ asset('images/logo-color.png') }}",
        "width": 512,
        "height": 512
    },
    "description": "Authentischer Austausch, Gemeinschaft und persönliches Wachstum für Männer in Niederbayern.",
    "address": {
        "@@type": "PostalAddress",
        "addressLocality": "Straubing",
        "addressRegion": "Bayern",
        "addressCountry": "DE"
    },
    "email": "hallo@@mens-circle.de",
    "areaServed": {
        "@@type": "Place",
        "name": "Niederbayern"
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
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "WebSite",
    "@@id": "{{ url('/') }}#website",
    "name": "Männerkreis Niederbayern/ Straubing",
    "url": "{{ url('/') }}",
    "description": "Authentischer Austausch, Gemeinschaft und persönliches Wachstum für Männer in Niederbayern.",
    "inLanguage": "de-DE",
    "publisher": {
        "@@id": "{{ url('/') }}#organization"
    },
    "potentialAction": {
        "@@type": "SearchAction",
        "target": "{{ url('/') }}?s={search_term_string}",
        "query-input": "required name=search_term_string"
    }
}
</script>
@endpush

@section('content')
    @if($page->contentBlocks->isNotEmpty())
        @foreach($page->contentBlocks as $block)
            @if($block->type === 'hero')
                <x-blocks.hero :block="$block" :page="$page" />
            @elseif($block->type === 'intro')
                <x-blocks.intro :block="$block" />
            @elseif($block->type === 'text_section')
                <x-blocks.text-section :block="$block" />
            @elseif($block->type === 'value_items')
                <x-blocks.value-items :block="$block" />
            @elseif($block->type === 'archetypes')
                <x-blocks.archetypes :block="$block" />
            @elseif($block->type === 'moderator')
                <x-blocks.moderator :block="$block" :page="$page" />
            @elseif($block->type === 'journey_steps')
                <x-blocks.journey-steps :block="$block" />
            @elseif($block->type === 'testimonials')
                @if($testimonials->isNotEmpty())
                    @include('components.blocks.testimonials', ['testimonials' => $testimonials])
                @endif
            @elseif($block->type === 'faq')
                <x-blocks.faq :block="$block" />
            @elseif($block->type === 'newsletter')
                <x-blocks.newsletter :block="$block" />
            @elseif($block->type === 'cta')
                <x-blocks.cta :block="$block" />
            @elseif($block->type === 'whatsapp_community')
                <x-blocks.whatsapp-community />
            @endif
        @endforeach
    @endif
@endsection
