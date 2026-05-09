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
  class="relative isolate overflow-hidden bg-[var(--bg-deep)] text-[var(--color-parchment)]"
  role="banner"
>
  @if ($media)
    {{ $media->img()->attributes([
        'class' => 'absolute inset-0 -z-10 h-full w-full object-cover opacity-40',
        'loading' => 'eager',
        'fetchpriority' => 'high',
        'aria-hidden' => 'true',
    ]) }}
  @endif

  {{-- Ambient breathing circles --}}
  <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
    <div
      class="absolute -top-20 -left-20 h-80 w-80 rounded-full bg-[var(--color-terracotta)]/30 blur-3xl animate-breathe"
    ></div>
    <div
      class="absolute top-1/3 right-0 h-72 w-72 rounded-full bg-[var(--color-sage)]/20 blur-3xl animate-breathe [animation-delay:2s]"
    ></div>
    <div
      class="absolute bottom-0 left-1/4 h-96 w-96 rounded-full bg-[var(--color-earth-warm)]/20 blur-3xl animate-breathe [animation-delay:4s]"
    ></div>
  </div>

  <div class="container-page flex min-h-[80vh] items-center py-24">
    <div class="max-w-3xl">
      @if (!empty($data['label']))
        <p x-reveal class="eyebrow text-[var(--color-terracotta-light)]">{{ $data['label'] }}</p>
      @endif

      @if (!empty($data['title']))
        <h1
          x-reveal
          class="font-display text-4xl font-semibold leading-[1.05] tracking-tight md:text-6xl lg:text-[clamp(3rem,7vw,8rem)]"
        >
          {!! $data['title'] !!}
        </h1>
      @endif

      <div
        class="mt-8 flex flex-col gap-6 md:flex-row md:items-end md:justify-between md:gap-12"
      >
        @if (!empty($data['description']))
          <p x-reveal class="max-w-xl text-lg text-[var(--color-sand)] leading-relaxed">{{ $data['description'] }}</p>
        @endif

        @if ($shouldShowButton)
          <div x-reveal class="flex flex-col items-start gap-2">
            <a
              href="{{ $resolvedButtonLink }}"
              class="btn btn-primary btn-large"
              >{{ $data['button_text'] }}</a
            >
            <span
              class="text-xs uppercase tracking-widest text-[var(--color-sand)]/70"
              aria-hidden="true"
              >Entdecken ↓</span
            >
          </div>
        @endif
      </div>
    </div>
  </div>
</section>
