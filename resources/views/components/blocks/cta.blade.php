@props(['block'])

@php
    $data = $block->data;
@endphp

<section class="section section--large cta-section">
    <div class="container">
        <div class="cta__content fade-in">
            @if(!empty($data['eyebrow']))
                <p class="eyebrow">{{ $data['eyebrow'] }}</p>
            @endif

            @if(!empty($data['title']))
                <h2 class="section-title cta__title">{!! $data['title'] !!}</h2>
            @endif

            @if(!empty($data['text']))
                <p class="cta__text">{{ $data['text'] }}</p>
            @endif

            @if(!empty($data['button_text']) && !empty($data['button_link']))
                @php
                    $isEventLink = str_contains($data['button_link'], route('event.show')) ||
                                   str_contains($data['button_link'], '/event');
                    $shouldShowButton = !$isEventLink || $hasNextEvent;
                    $resolvedButtonLink = $isEventLink ? $nextEventUrl : $data['button_link'];
                @endphp

                @if($shouldShowButton)
                    <a
                        href="{{ $resolvedButtonLink }}"
                        class="btn btn--primary btn--large"
                        data-umami-event="cta-click"
                        data-umami-event-location="cta-block"
                        data-umami-event-text="{{ $data['button_text'] }}"
                    >
                        {{ $data['button_text'] }}
                    </a>
                @endif
            @endif
        </div>
    </div>
</section>
