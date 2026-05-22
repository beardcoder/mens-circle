@props (['block'])

@php
    $data = $block->data;
    $anchor = $data['anchor'] ?? Str::slug($data['title'] ?? '');
@endphp

<section class="section" @if ($anchor) id="{{ $anchor }}" @endif>
  <div class="container--narrow container">
    <div class="section-header section-header--start">
      @if (!empty($data['eyebrow']))
        <p class="eyebrow">{{ $data['eyebrow'] }}</p>
      @endif

      @if (!empty($data['title']))
        <h2 class="section-title">{{ $data['title'] }}</h2>
      @endif
    </div>

    @if (!empty($data['content']))
      <div class="section__content">{!! $data['content'] !!}</div>
    @endif
  </div>
</section>
