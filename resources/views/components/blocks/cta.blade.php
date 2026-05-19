@props (['block'])

@php
    $data = $block->data;
@endphp

<section class="section section--large cta-section">
  <div class="container">
    <div class="cta__content">
      @if (!empty($data['eyebrow']))
        <p class="eyebrow">{{ $data['eyebrow'] }}</p>
      @endif

      @if (!empty($data['title']))
        <h2 class="section-title cta__title">
          {!! $data['title'] !!}
        </h2>
      @endif

      @if (!empty($data['text']))
        <p class="cta__text">{{ $data['text'] }}</p>
      @endif

      @if (!empty($data['button_text']) && !empty($data['button_link']))
        @php
            $button = App\Support\CmsButtonLink::resolve($data['button_link'], $hasNextEvent, $nextEventUrl);
        @endphp
        @if ($button->shouldShow)
          <a
            href="{{ $button->href }}"
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
