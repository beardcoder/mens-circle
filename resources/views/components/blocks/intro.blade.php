@props (['block'])

@php
    $data = $block->data;
    $anchor = $data['anchor'] ?? 'ueber';
@endphp

<section class="intro-section" id="{{ $anchor }}" aria-labelledby="intro-title">
  <div class="intro__layout">
    <div class="intro__left">
      @if (!empty($data['eyebrow']))
        <p class="eyebrow" data-reveal="up">{{ $data['eyebrow'] }}</p>
      @endif

      @if (!empty($data['title']))
        <h2
          class="section-title intro__title"
          id="intro-title"
          data-reveal="blur"
          data-reveal-delay="80"
        >
          {!! $data['title'] !!}
        </h2>
      @endif

      @if (!empty($data['text']))
        <p class="intro__text" data-reveal="up" data-reveal-delay="180">{{ $data['text'] }}</p>
      @endif

      @if (!empty($data['values']) && is_array($data['values']))
        <div class="intro__values" data-reveal-group="90">
          @foreach ($data['values'] as $value)
            <div class="value-item" data-reveal="up">
              @if (!empty($value['number']))
                <span class="value-item__number">{{ $value['number'] }}</span>
              @endif
              @if (!empty($value['title']))
                <h3 class="value-item__title">{{ $value['title'] }}</h3>
              @endif
              @if (!empty($value['description']))
                <p class="value-item__description">{{ $value['description'] }}</p>
              @endif
            </div>
          @endforeach
        </div>
      @endif
    </div>

    <div class="intro__image-area" data-reveal="left" data-reveal-delay="120">
      <div class="intro__image-circles" aria-hidden="true">
        <div class="intro__image-ring intro__image-ring--outer"></div>
        <div class="intro__image-ring intro__image-ring--inner"></div>
      </div>
      @if (!empty($data['quote']))
        <p class="intro__image-text">{!! $data['quote'] !!}</p>
      @endif
    </div>
  </div>
</section>
