@props (['block'])

@php
    $data = $block->data;
    $anchor = $data['anchor'] ?? null;
@endphp

<section class="section values-section" @if ($anchor) id="{{ $anchor }}" @endif>
  <div class="container">
    <div class="section-header">
      @if (!empty($data['eyebrow']))
        <p class="eyebrow">{{ $data['eyebrow'] }}</p>
      @endif

      @if (!empty($data['title']))
        <h2 class="section-title">{{ $data['title'] }}</h2>
      @endif
    </div>

    @if (!empty($data['items']) && is_array($data['items']))
      <div class="intro__values" data-reveal-stagger>
        @foreach ($data['items'] as $item)
          <div class="value-item" data-reveal="left">
            @if (!empty($item['number']))
              <span class="value-item__number">{{ $item['number'] }}</span>
            @endif

            @if (!empty($item['title']))
              <h3 class="value-item__title">{{ $item['title'] }}</h3>
            @endif

            @if (!empty($item['description']))
              <p class="value-item__description">{{ $item['description'] }}</p>
            @endif
          </div>
        @endforeach
      </div>
    @endif
  </div>
</section>
