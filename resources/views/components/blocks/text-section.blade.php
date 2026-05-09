@props (['block'])

@php
    $data = $block->data;
@endphp

<section class="section-y" id="{{ Str::slug($data['title'] ?? '') }}">
  <div class="container-narrow">
    <div x-reveal class="mb-8">
      @if (!empty($data['eyebrow']))
        <p class="eyebrow">{{ $data['eyebrow'] }}</p>
      @endif
      @if (!empty($data['title']))
        <h2 class="section-title">{{ $data['title'] }}</h2>
      @endif
    </div>

    @if (!empty($data['content']))
      <div x-reveal class="prose-block text-[var(--fg-muted)]">
        {!! $data['content'] !!}
      </div>
    @endif
  </div>
</section>
