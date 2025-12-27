<section class="section section--large cta-section">
    <div class="container">
        <div class="cta__content fade-in">
            @if(!empty($block['data']['eyebrow']))
                <p class="eyebrow">{{ $block['data']['eyebrow'] }}</p>
            @endif

            @if(!empty($block['data']['title']))
                <h2 class="section-title cta__title">{!! $block['data']['title'] !!}</h2>
            @endif

            @if(!empty($block['data']['text']))
                <p class="cta__text">{{ $block['data']['text'] }}</p>
            @endif

            @if(!empty($block['data']['button_text']) && !empty($block['data']['button_link']))
                @php
                    $isEventLink = str_contains($block['data']['button_link'], route('event.show')) ||
                                   str_contains($block['data']['button_link'], '/event');
                    $shouldShowButton = !$isEventLink || $hasNextEvent;
                @endphp

                @if($shouldShowButton)
                    <a href="{{ $block['data']['button_link'] }}" class="btn btn--primary btn--large" data-m:click="action=cta_click;element=button;target=cta_section;location=page_cta">
                        {{ $block['data']['button_text'] }}
                    </a>
                @endif
            @endif
        </div>
    </div>
</section>
