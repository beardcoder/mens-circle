@props([
    'block',
    'page' => null,
])

@php
    $data = $block->data;
    $media = $block->getFieldMedia('background_image');

    if ($media) {
        $media->name = $data['title'] ?? 'Männerkreis Niederbayern/ Straubing';
    }
@endphp

<section class="hero" role="banner">
    <div class="hero__bg">
        @if($media)
            {{ $media->img()->attributes([
                'class' => 'hero__bg-image',
                'loading' => 'eager',
                'fetchpriority' => 'high',
                'aria-hidden' => 'true',
            ]) }}
        @endif
    </div>

    <div class="hero__circles" aria-hidden="true">
        <div class="hero__circle hero__circle--1"></div>
        <div class="hero__circle hero__circle--2"></div>
        <div class="hero__circle hero__circle--3"></div>
        <div class="hero__circle hero__circle--4"></div>
    </div>

    <div class="container">
        <div class="hero__content">
            @if(!empty($data['label']))
                <p class="hero__label">{{ $data['label'] }}</p>
            @endif

            @if(!empty($data['title']))
                <h1 class="hero__title">
                    {!! $data['title'] !!}
                </h1>
            @endif

            <div class="hero__bottom">
                @if(!empty($data['description']))
                    <p class="hero__description">
                        {{ $data['description'] }}
                    </p>
                @endif

                @if(!empty($data['button_text']) && !empty($data['button_link']))
                    @php
                        $isEventLink = str_contains($data['button_link'], route('event.show')) ||
                                       str_contains($data['button_link'], '/event');
                        $shouldShowButton = !$isEventLink || $hasNextEvent;
                    @endphp

                    @if($shouldShowButton)
                        <div class="hero__cta fade-in-delay-3">
                            <a href="{{ $data['button_link'] }}" class="btn btn--primary btn--large">
                                {{ $data['button_text'] }}
                            </a>
                            <div class="hero__scroll fade-in-delay-4">
                                <span>Entdecken</span>
                                <div class="hero__scroll-line" aria-hidden="true"></div>
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</section>
