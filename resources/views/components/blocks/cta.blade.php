@props (['block'])

@php
    $data = $block->data;
    $isEventLink = !empty($data['button_link']) && (str_contains($data['button_link'], route('event.show')) || str_contains($data['button_link'], '/event'));
    $shouldShowButton = !empty($data['button_text']) && !empty($data['button_link']) && (!$isEventLink || $hasNextEvent);
    $resolvedButtonLink = $isEventLink ? $nextEventUrl : ($data['button_link'] ?? '#');
@endphp

<section class="section-y-lg">
  <div class="px-0">
    <div
      class="relative isolate flex flex-col items-center gap-8 overflow-hidden bg-[var(--color-earth-deep)] px-6 py-16 text-center text-[var(--color-parchment)] md:px-12 md:py-24 animate-reveal-up timeline-view animate-range-[entry_5%_cover_25%]"
    >
      {{-- Decorative circles --}}
      <span
        class="pointer-events-none absolute -top-16 -right-16 block h-72 w-72 rounded-full border border-[var(--color-sand)]/12 animate-breathe [animation-duration:24s]"
        aria-hidden="true"
      ></span>
      <span
        class="pointer-events-none absolute -bottom-32 -left-20 block h-96 w-96 rounded-full border border-[var(--color-sand)]/8 animate-breathe [animation-delay:-6s] [animation-duration:30s]"
        aria-hidden="true"
      ></span>
      <span
        class="pointer-events-none absolute inset-0 -z-10 [background:radial-gradient(ellipse_60%_50%_at_50%_50%,color-mix(in_oklch,var(--accent)_10%,transparent)_0%,transparent_72%)]"
        aria-hidden="true"
      ></span>

      <div
        class="relative section-header mx-auto mb-0 max-w-[54rem] items-center"
      >
        @if (!empty($data['eyebrow']))
          <p class="eyebrow text-[var(--color-terracotta-light)]/90">{{ $data['eyebrow'] }}</p>
        @endif
        @if (!empty($data['title']))
          <h2
            class="section-title-lg split-title text-[var(--color-parchment)]"
          >
            {!! $data['title'] !!}
          </h2>
        @endif
        @if (!empty($data['text']))
          <p class="section-intro mx-auto text-[var(--color-sand)]">{{ $data['text'] }}</p>
        @endif
        @if ($shouldShowButton)
          <a
            href="{{ $resolvedButtonLink }}"
            class="btn btn-primary btn-large mt-8"
            data-umami-event="cta-click"
            data-umami-event-location="cta-block"
            data-umami-event-text="{{ $data['button_text'] }}"
            >{{ $data['button_text'] }}</a
          >
        @endif
      </div>
    </div>
  </div>
</section>
