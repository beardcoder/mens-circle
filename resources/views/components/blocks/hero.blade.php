<section class="hero">
    <div class="hero__bg"
        @if($block->hasMedia('images'))
            style="background-image: url('{{ $block->getFirstMediaUrl('images', 'responsive') }}'); background-size: cover; background-position: center;"
        @endif
    ></div>

    <div class="hero__circles" aria-hidden="true">
        <div class="hero__circle hero__circle--1"></div>
        <div class="hero__circle hero__circle--2"></div>
        <div class="hero__circle hero__circle--3"></div>
        <div class="hero__circle hero__circle--4"></div>
    </div>

    <div class="container">
        <div class="hero__content">
            @if(!empty($block->data['label']))
                <p class="hero__label fade-in">{{ $block->data['label'] }}</p>
            @endif

            @if(!empty($block->data['title']))
                <h1 class="hero__title fade-in fade-in-delay-1">
                    {!! $block->data['title'] !!}
                </h1>
            @endif

            <div class="hero__bottom fade-in fade-in-delay-2">
                @if(!empty($block->data['description']))
                    <p class="hero__description">
                        {{ $block->data['description'] }}
                    </p>
                @endif

                @if(!empty($block->data['button_text']) && !empty($block->data['button_link']))
                    @php
                        $isEventLink = str_contains($block->data['button_link'], route('event.show')) ||
                                       str_contains($block->data['button_link'], '/event');
                        $shouldShowButton = !$isEventLink || $hasNextEvent;
                    @endphp

                    @if($shouldShowButton)
                        <div class="hero__cta">
                            <a href="{{ $block->data['button_link'] }}" class="btn btn--primary btn--large">
                                {{ $block->data['button_text'] }}
                            </a>
                            <div class="hero__scroll">
                                <span>Entdecken</span>
                                <div class="hero__scroll-line"></div>
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    @if($block->hasMedia('images'))
        <picture style="display: none;">
            <source srcset="{{ $block->getFirstMediaUrl('images', 'responsive-avif') }}" type="image/avif">
            <source srcset="{{ $block->getFirstMediaUrl('images', 'responsive') }}" type="image/webp">
        </picture>
    @endif
</section>
