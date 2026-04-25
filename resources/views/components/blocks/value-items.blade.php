@props (['block'])

@php
    $data = $block->data;
@endphp

<section class="section values-section">
  <div class="container">
    <div class="section-header" data-anim-group>
      @if (!empty($data['eyebrow']))
        <p class="eyebrow" data-anim="rise">{{ $data['eyebrow'] }}</p>
      @endif

      @if (!empty($data['title']))
        <h2 class="section-title" data-anim="rise">{{ $data['title'] }}</h2>
      @endif
    </div>

    @if (!empty($data['items']) && is_array($data['items']))
      <div class="intro__values" data-anim-group>
        @foreach ($data['items'] as $item)
          <div class="value-item" data-anim="rise">
            @if (!empty($item['number']))
              <span class="value-item__number">{{ $item['number'] }}</span>
            @endif

            <div class="value-item__content">
              @if (!empty($item['title']))
                <h3>{{ $item['title'] }}</h3>
              @endif

              @if (!empty($item['description']))
                <p>{{ $item['description'] }}</p>
              @endif
            </div>
          </div>
        @endforeach
      </div>
    @endif
  </div>
</section>
