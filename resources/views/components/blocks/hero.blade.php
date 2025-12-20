<section class="hero">
    <div class="hero__bg"></div>

    <div class="hero__circles" aria-hidden="true">
        <div class="hero__circle hero__circle--1"></div>
        <div class="hero__circle hero__circle--2"></div>
        <div class="hero__circle hero__circle--3"></div>
        <div class="hero__circle hero__circle--4"></div>
    </div>

    <div class="container">
        <div class="hero__content">
            @if(!empty($block['label']))
                <p class="hero__label fade-in">{{ $block['label'] }}</p>
            @endif

            @if(!empty($block['title']))
                <h1 class="hero__title fade-in fade-in-delay-1">
                    {!! $block['title'] !!}
                </h1>
            @endif

            <div class="hero__bottom fade-in fade-in-delay-2">
                @if(!empty($block['description']))
                    <p class="hero__description">
                        {{ $block['description'] }}
                    </p>
                @endif

                @if(!empty($block['button_text']) && !empty($block['button_link']))
                    <div class="hero__cta">
                        <a href="{{ $block['button_link'] }}" class="btn btn--primary btn--large">
                            {{ $block['button_text'] }}
                        </a>
                        <div class="hero__scroll">
                            <span>Entdecken</span>
                            <div class="hero__scroll-line"></div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
