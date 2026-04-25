@props (['block'])

@php
    $data = $block->data;
@endphp

<section class="section" id="{{ Str::slug($data['title'] ?? '') }}">
  <div class="container--narrow container">
    <div class="section-header section-header--start" data-anim-group>
      @if (!empty($data['eyebrow']))
        <p class="eyebrow" data-anim="rise">{{ $data['eyebrow'] }}</p>
      @endif

      @if (!empty($data['title']))
        <h2 class="section-title" data-anim="rise">{{ $data['title'] }}</h2>
      @endif
    </div>

    @if (!empty($data['content']))
      <div class="section__content" data-anim="rise">
        {!! $data['content'] !!}
      </div>
    @endif
  </div>
</section>
