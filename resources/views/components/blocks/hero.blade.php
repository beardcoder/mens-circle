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
            @if(!empty($block['subtitle']))
                <p class="hero__label fade-in">{{ $block['subtitle'] }}</p>
            @endif

            @if(!empty($block['title']))
                <h1 class="hero__title fade-in fade-in-delay-1">
                    {!! nl2br(e($block['title'])) !!}
                </h1>
            @endif

            @if(!empty($block['description']))
                <div class="hero__bottom fade-in fade-in-delay-2">
                    <p class="hero__description">
                        {{ $block['description'] }}
                    </p>
                </div>
            @endif
        </div>
    </div>
</section>
