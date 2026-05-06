@props (['block'])

@php
    $data = $block->data;
@endphp

<section class="intro-section" id="ueber" aria-labelledby="intro-title">
  <span class="story-chapter" aria-hidden="true">Kapitel 02 / Ankommen</span>
  <div class="intro__layout">
    <div class="intro__left" data-anim-group>
      @if (!empty($data['eyebrow']))
        <p class="eyebrow" data-anim="rise">{{ $data['eyebrow'] }}</p>
      @endif

      @if (!empty($data['title']))
        <h2
          class="section-title intro__title"
          id="intro-title"
          data-anim="rise"
        >
          {!! $data['title'] !!}
        </h2>
      @endif

      @if (!empty($data['text']))
        <p class="intro__text" data-anim="rise">{{ $data['text'] }}</p>
      @endif

      @if (!empty($data['values']) && is_array($data['values']))
        <div class="intro__values" data-anim-group>
          @foreach ($data['values'] as $value)
            <div class="value-item" data-anim="rise">
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

    <div class="intro__image-area" data-anim="scale">
      <div class="intro__image-circles" aria-hidden="true"></div>
      @if (!empty($data['quote']))
        <p class="intro__image-text" data-anim="fade">{!! $data['quote'] !!}</p>
      @endif
    </div>
  </div>
</section>
