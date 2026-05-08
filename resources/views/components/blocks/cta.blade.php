@props (['block'])

@php
    $data = $block->data;
@endphp

<section class="relative overflow-hidden py-xl text-center bg-bg-tertiary cta-section">
  <div class="w-full max-w-container px-md mx-auto">
    <div class="relative z-10 max-w-[700px] mx-auto animate-scale-up">
      @if (!empty($data['eyebrow']))
        <p class="eyebrow">{{ $data['eyebrow'] }}</p>
      @endif

      @if (!empty($data['title']))
        <h2 class="section-title cta__title">{!! $data['title'] !!}</h2>
      @endif

      @if (!empty($data['text']))
        <p class="mb-lg text-[length:var(--text-section-body-lg)] leading-relaxed text-text-secondary">
          {{ $data['text'] }}
        </p>
      @endif

      @if (!empty($data['button_text']) && !empty($data['button_link']))
        @php
                    $isEventLink = str_contains($data['button_link'], route('event.show')) ||
                                   str_contains($data['button_link'], '/event');
                    $shouldShowButton = !$isEventLink || $hasNextEvent;
                    $resolvedButtonLink = $isEventLink ? $nextEventUrl : $data['button_link'];
                @endphp
        @if ($shouldShowButton)
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
