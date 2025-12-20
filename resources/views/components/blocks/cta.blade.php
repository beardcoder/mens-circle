<section class="section section--large cta-section">
    <div class="container">
        <div class="cta__content fade-in">
            @if(!empty($block['eyebrow']))
                <p class="cta__eyebrow">{{ $block['eyebrow'] }}</p>
            @endif

            @if(!empty($block['title']))
                <h2 class="cta__title">{!! $block['title'] !!}</h2>
            @endif

            @if(!empty($block['text']))
                <p class="cta__text">{{ $block['text'] }}</p>
            @endif

            @if(!empty($block['button_text']) && !empty($block['button_link']))
                <a href="{{ $block['button_link'] }}" class="btn btn--primary btn--large">
                    {{ $block['button_text'] }}
                </a>
            @endif
        </div>
    </div>
</section>
