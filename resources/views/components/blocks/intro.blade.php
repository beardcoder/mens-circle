@props (['block'])

@php
    $data = $block->data;
@endphp

<section class="section-y" id="ueber" aria-labelledby="intro-title">
  <div class="container-page grid gap-12 md:grid-cols-2 md:items-center">
    <div x-reveal>
      @if (!empty($data['eyebrow']))
        <p class="eyebrow">{{ $data['eyebrow'] }}</p>
      @endif

      @if (!empty($data['title']))
        <h2 class="section-title-lg" id="intro-title">
          {!! $data['title'] !!}
        </h2>
      @endif

      @if (!empty($data['text']))
        <p class="mt-6 max-w-prose text-[var(--text-section-body-lg)] text-[var(--fg-muted)] leading-relaxed">{{ $data['text'] }}</p>
      @endif

      @if (!empty($data['values']) && is_array($data['values']))
        <div class="mt-10 grid gap-8 sm:grid-cols-2">
          @foreach ($data['values'] as $value)
            <div x-reveal class="flex flex-col gap-2">
              @if (!empty($value['number']))
                <span
                  class="font-display text-4xl font-semibold text-[var(--accent)]"
                  >{{ $value['number'] }}</span
                >
              @endif
              @if (!empty($value['title']))
                <h3 class="font-display text-xl font-medium">
                  {{ $value['title'] }}
                </h3>
              @endif
              @if (!empty($value['description']))
                <p class="text-sm text-[var(--fg-muted)]">{{ $value['description'] }}</p>
              @endif
            </div>
          @endforeach
        </div>
      @endif
    </div>

    <div x-reveal.zoom class="relative aspect-square w-full max-w-md mx-auto">
      <div
        class="absolute inset-0 rounded-full bg-gradient-to-br from-[var(--color-terracotta)]/30 to-[var(--color-earth-warm)]/20 blur-2xl"
      ></div>
      <div
        class="absolute inset-6 rounded-full border border-[var(--border)] animate-breathe"
      ></div>
      <div
        class="absolute inset-12 rounded-full border border-[var(--accent)]/40 animate-breathe [animation-delay:2s]"
      ></div>
      @if (!empty($data['quote']))
        <p class="relative grid h-full place-items-center px-8 text-center font-display text-2xl italic leading-snug text-[var(--fg)]">{!! $data['quote'] !!}</p>
      @endif
    </div>
  </div>
</section>
