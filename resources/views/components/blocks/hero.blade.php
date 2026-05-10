@props ([
    'block',
    'page' => null,
])

@php
    $data = $block->data;
    $media = $block->getFieldMedia('background_image');

    if ($media) {
        $media->name = $data['title'] ?? 'Männerkreis Niederbayern/ Straubing';
    }

    $isEventLink = !empty($data['button_link']) && (str_contains($data['button_link'], route('event.show')) || str_contains($data['button_link'], '/event'));
    $shouldShowButton = !empty($data['button_text']) && !empty($data['button_link']) && (!$isEventLink || $hasNextEvent);
    $resolvedButtonLink = $isEventLink ? $nextEventUrl : ($data['button_link'] ?? '#');
@endphp

<section
  data-hero
  role="banner"
  class="relative isolate flex min-h-[100svh] items-end overflow-hidden bg-gradient-to-b from-[var(--color-earth-deep)] via-[var(--color-earth-dark)] to-[var(--color-earth-deep)] pb-20 text-[var(--color-parchment)] md:pb-24"
  style="min-block-size: min(880px, 100svh)"
>
  {{-- Bg image --}}
  @if ($media)
    {{ $media->img()->attributes([
        'class' => 'absolute inset-0 -z-10 h-full w-full object-cover opacity-24 mix-blend-luminosity',
        'loading' => 'eager',
        'fetchpriority' => 'high',
        'aria-hidden' => 'true',
    ]) }}
  @endif

  {{-- Warm radial glow --}}
  <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
    <div
      class="absolute inset-0 [background:radial-gradient(ellipse_80%_60%_at_70%_30%,color-mix(in_oklch,var(--accent)_14%,transparent)_0%,transparent_52%),radial-gradient(ellipse_60%_50%_at_20%_80%,color-mix(in_oklch,var(--accent)_8%,transparent)_0%,transparent_43%)]"
    ></div>
  </div>

  {{-- Decorative circles --}}
  <div class="hero-decor" aria-hidden="true">
    <span
      class="-top-[15vw] -right-[25vw] h-[70vw] w-[70vw] animate-breathe [animation-duration:24s]"
    ></span>
    <span
      class="-top-[5vw] -right-[15vw] h-[50vw] w-[50vw] animate-breathe [animation-delay:-5s] [animation-duration:30s]"
    ></span>
    <span
      class="top-[2vw] -right-[8vw] h-[35vw] w-[35vw] animate-breathe [animation-delay:-10s] [animation-duration:34s]"
    ></span>
    <span
      class="-bottom-[45vw] -left-[45vw] h-[90vw] w-[90vw] animate-breathe [animation-delay:-3s] [animation-duration:38s]"
    ></span>
  </div>

  <div class="container-page relative z-10 w-full">
    @if (!empty($data['label']))
      <p class="eyebrow text-[var(--color-terracotta-light)]/85 animate-reveal-up timeline-view animate-range-[entry_5%_cover_25%]">{{ $data['label'] }}</p>
    @endif

    @if (!empty($data['title']))
      <h1
        class="hero-title hero-title-emphasis split-title max-w-[22ch] md:max-w-[19ch] xl:max-w-[17ch] text-[var(--color-parchment)] animate-reveal-up timeline-view animate-range-[entry_5%_cover_25%]"
      >
        {!! $data['title'] !!}
      </h1>
    @endif

    <div
      class="mt-10 grid gap-10 md:mt-12 md:grid-cols-[minmax(0,1fr)_auto] md:items-end"
    >
      @if (!empty($data['description']))
        <p class="max-w-[58ch] text-base leading-[1.92] text-[var(--color-sand)] md:text-lg animate-reveal-up timeline-view animate-range-[entry_5%_cover_25%]">{{ $data['description'] }}</p>
      @endif

      @if ($shouldShowButton)
        <div
          class="flex flex-col items-start gap-3 md:items-end animate-reveal-up timeline-view animate-range-[entry_5%_cover_25%]"
        >
          <a
            href="{{ $resolvedButtonLink }}"
            class="btn btn-primary btn-large"
            >{{ $data['button_text'] }}</a
          >
          <span
            class="inline-flex items-center gap-2 text-[0.65rem] font-semibold uppercase tracking-[0.24em] text-[var(--color-sand)]/60"
            aria-hidden="true"
          >
            <span class="h-px w-10 bg-[var(--color-sand)]/35"></span>
            Nächster Schritt
          </span>
        </div>
      @endif
    </div>
  </div>
</section>
