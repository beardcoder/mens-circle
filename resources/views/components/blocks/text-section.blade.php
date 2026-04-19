@props (['block'])

@php
    $data = $block->data;
@endphp

<section class="section" id="{{ Str::slug($data['title'] ?? '') }}">
  <div class="container--narrow container">
    <div class="section__header" data-reveal>
      @if (!empty($data['eyebrow']))
        <p class="eyebrow">{{ $data['eyebrow'] }}</p>
      @endif

      @if (!empty($data['title']))
        <h2 class="section-title">{{ $data['title'] }}</h2>
      @endif
    </div>

    @if (!empty($data['content']))
      <div class="section__content" data-reveal data-reveal-delay="1">
        {!! $data['content'] !!}
      </div>
    @endif
  </div>
</section>
