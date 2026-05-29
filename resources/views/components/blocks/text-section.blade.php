@props (['block'])

@php
    $data = $block->data;
    $anchor = $data['anchor'] ?? Str::slug($data['title'] ?? '');
@endphp

<section class="section" @if ($anchor) id="{{ $anchor }}" @endif>
  <div class="container--narrow container">
    <div class="section-header section-header--start" data-reveal-group>
      @if (!empty($data['eyebrow']))
        <p class="eyebrow" data-reveal="up">{{ $data['eyebrow'] }}</p>
      @endif

      @if (!empty($data['title']))
        <h2 class="section-title" data-reveal="blur">{{ $data['title'] }}</h2>
      @endif
    </div>

    @if (!empty($data['content']))
      <div class="section__content" data-reveal="up" data-reveal-delay="120">
        {!! $data['content'] !!}
      </div>
    @endif
  </div>
</section>
