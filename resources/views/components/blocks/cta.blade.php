<section class="section cta-section">
    <div class="container">
        <div class="cta-card fade-in">
            @if(!empty($block['eyebrow']))
                <p class="section__eyebrow">{{ $block['eyebrow'] }}</p>
            @endif

            @if(!empty($block['title']))
                <h2>{{ $block['title'] }}</h2>
            @endif

            @if(!empty($block['text']))
                <p>{{ $block['text'] }}</p>
            @endif

            @if(!empty($block['button_text']) && !empty($block['button_link']))
                <a href="{{ $block['button_link'] }}" class="btn btn--primary btn--large">
                    {{ $block['button_text'] }}
                </a>
            @endif
        </div>
    </div>
</section>
