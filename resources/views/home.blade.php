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
    @if($page->contentBlocks->isNotEmpty())
        @foreach($page->contentBlocks as $block)
            @if($block->type === App\Enums\ContentBlockType::Hero)
                <x-blocks.hero :block="$block" />
            @elseif($block->type === App\Enums\ContentBlockType::Intro)
                <x-blocks.intro :block="$block" />
            @elseif($block->type === App\Enums\ContentBlockType::TextSection)
                <x-blocks.text-section :block="$block" />
            @elseif($block->type === App\Enums\ContentBlockType::ValueItems)
                <x-blocks.value-items :block="$block" />
            @elseif($block->type === App\Enums\ContentBlockType::Moderator)
                <x-blocks.moderator :block="$block" />
            @elseif($block->type === App\Enums\ContentBlockType::JourneySteps)
                <x-blocks.journey-steps :block="$block" />
            @elseif($block->type === App\Enums\ContentBlockType::Faq)
                <x-blocks.faq :block="$block" />
            @elseif($block->type === App\Enums\ContentBlockType::Newsletter)
                <x-blocks.newsletter :block="$block" />
            @elseif($block->type === App\Enums\ContentBlockType::Cta)
                <x-blocks.cta :block="$block" />
            @endif
        @endforeach
    @endif
@endsection
