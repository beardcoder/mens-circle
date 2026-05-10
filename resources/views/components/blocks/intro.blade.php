@props (['block'])

@php
    $data = $block->data;
@endphp

<section class="section-y-lg" id="ueber" aria-labelledby="intro-title">
  <div
    class="container-page grid gap-16 md:grid-cols-[1.1fr_1fr] md:items-center"
  >
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
        <p class="mt-8 max-w-prose text-lg leading-[1.85] text-[var(--fg-muted)]">{{ $data['text'] }}</p>
      @endif

      @if (!empty($data['values']) && is_array($data['values']))
        <div class="mt-12 grid gap-10 sm:grid-cols-2">
          @foreach ($data['values'] as $value)
            <div x-reveal class="border-l-2 border-[var(--accent)]/30 pl-5">
              @if (!empty($value['number']))
                <span
                  class="block font-display text-5xl font-medium leading-none text-[var(--accent)]"
                  >{{ $value['number'] }}</span
                >
              @endif
              @if (!empty($value['title']))
                <h3 class="mt-3 font-display text-xl font-medium">
                  {{ $value['title'] }}
                </h3>
              @endif
              @if (!empty($value['description']))
                <p class="mt-2 text-sm leading-relaxed text-[var(--fg-muted)]">{{ $value['description'] }}</p>
              @endif
            </div>
          @endforeach
        </div>
      @endif
    </div>

    <div
      x-reveal.zoom
      class="relative aspect-square w-full max-w-md mx-auto md:mx-0 md:ml-auto"
    >
      <div
        class="absolute inset-0 rounded-full bg-gradient-to-br from-[var(--color-terracotta)]/30 via-transparent to-[var(--color-earth-warm)]/15 blur-3xl"
      ></div>
      <div
        class="absolute inset-0 rounded-full border border-[var(--border)] animate-breathe"
      ></div>
      <div
        class="absolute inset-8 rounded-full border border-[var(--accent)]/40 animate-breathe [animation-delay:2s] [animation-duration:9s]"
      ></div>
      <div
        class="absolute inset-20 rounded-full border border-[var(--accent)]/20 animate-breathe [animation-delay:4s] [animation-duration:11s]"
      ></div>
      @if (!empty($data['quote']))
        <p class="relative grid h-full place-items-center px-10 text-center font-display text-2xl italic leading-snug text-[var(--fg)] md:text-3xl">{!! $data['quote'] !!}</p>
      @endif
    </div>
  </div>
</section>
