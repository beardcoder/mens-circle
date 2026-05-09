@props (['block'])

@php
    $data = $block->data;
    $isEventLink = !empty($data['button_link']) && (str_contains($data['button_link'], route('event.show')) || str_contains($data['button_link'], '/event'));
    $shouldShowButton = !empty($data['button_text']) && !empty($data['button_link']) && (!$isEventLink || $hasNextEvent);
    $resolvedButtonLink = $isEventLink ? $nextEventUrl : ($data['button_link'] ?? '#');
@endphp

<section class="section-y-lg">
  <div class="container-page">
    <div
      x-reveal
      class="mx-auto flex max-w-3xl flex-col items-center gap-6 rounded-3xl bg-[var(--bg-alt)] p-12 text-center md:p-16"
    >
      @if (!empty($data['eyebrow']))
        <p class="eyebrow">{{ $data['eyebrow'] }}</p>
      @endif
      @if (!empty($data['title']))
        <h2 class="section-title-lg">{!! $data['title'] !!}</h2>
      @endif
      @if (!empty($data['text']))
        <p class="text-lg text-[var(--fg-muted)] max-w-2xl">{{ $data['text'] }}</p>
      @endif
      @if ($shouldShowButton)
        <a
          href="{{ $resolvedButtonLink }}"
          class="btn btn-primary btn-large mt-2"
          data-umami-event="cta-click"
          data-umami-event-location="cta-block"
          data-umami-event-text="{{ $data['button_text'] }}"
          >{{ $data['button_text'] }}</a
        >
      @endif
    </div>
  </div>
</section>
