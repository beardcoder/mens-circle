@props (['block'])

@php
    $data = $block->data;
@endphp

<section class="section-y" id="{{ Str::slug($data['title'] ?? '') }}">
  <div class="container-narrow">
    <div
      class="mb-8 animate-reveal-up timeline-view animate-range-[entry_5%_cover_25%]"
    >
      @if (!empty($data['eyebrow']))
        <p class="eyebrow">{{ $data['eyebrow'] }}</p>
      @endif
      @if (!empty($data['title']))
        <h2 class="section-title">{{ $data['title'] }}</h2>
      @endif
    </div>

    @if (!empty($data['content']))
      <div
        class="prose-block text-[var(--fg-muted)] animate-reveal-up timeline-view animate-range-[entry_5%_cover_25%]"
      >
        {!! $data['content'] !!}
      </div>
    @endif
  </div>
</section>
