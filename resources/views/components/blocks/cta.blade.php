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
      x-reveal
      class="relative isolate flex flex-col items-center gap-8 overflow-hidden bg-[var(--color-earth-deep)] p-12 text-center text-[var(--color-parchment)] md:p-24"
    >
      {{-- Decorative circles --}}
      <span
        class="pointer-events-none absolute -top-16 -right-16 block h-72 w-72 rounded-full border border-[var(--color-sand)]/15 animate-breathe [animation-duration:18s]"
        aria-hidden="true"
      ></span>
      <span
        class="pointer-events-none absolute -bottom-32 -left-20 block h-96 w-96 rounded-full border border-[var(--color-sand)]/10 animate-breathe [animation-delay:-6s] [animation-duration:24s]"
        aria-hidden="true"
      ></span>
      <span
        class="pointer-events-none absolute inset-0 -z-10 [background:radial-gradient(ellipse_60%_50%_at_50%_50%,color-mix(in_oklch,var(--accent)_15%,transparent)_0%,transparent_70%)]"
        aria-hidden="true"
      ></span>

      <div class="relative">
        @if (!empty($data['eyebrow']))
          <p class="eyebrow text-[var(--color-terracotta-light)]">{{ $data['eyebrow'] }}</p>
        @endif
        @if (!empty($data['title']))
          <h2 class="section-title-lg text-[var(--color-parchment)]">
            {!! $data['title'] !!}
          </h2>
        @endif
        @if (!empty($data['text']))
          <p class="mt-6 mx-auto max-w-2xl text-lg leading-[1.85] text-[var(--color-sand)]">{{ $data['text'] }}</p>
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
