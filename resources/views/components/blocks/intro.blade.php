@props (['block'])

@php
    $data = $block->data;
@endphp

<section
  class="section-y-lg editorial-light"
  id="ueber"
  aria-labelledby="intro-title"
>
  <div
    class="container-page grid gap-14 lg:grid-cols-[1fr_1fr] lg:items-start lg:gap-20"
  >
    <div
      class="animate-reveal-up timeline-view animate-range-[entry_5%_cover_25%]"
    >
      @if (!empty($data['eyebrow']))
        <p class="eyebrow">{{ $data['eyebrow'] }}</p>
      @endif

      @if (!empty($data['title']))
        <h2 class="section-title-lg split-title max-w-[16ch]" id="intro-title">
          {!! $data['title'] !!}
        </h2>
      @endif

      @if (!empty($data['text']))
        <p class="section-intro mt-7">{{ $data['text'] }}</p>
      @endif

      @if (!empty($data['values']) && is_array($data['values']))
        <div class="mt-12 grid gap-4 sm:grid-cols-2">
          @foreach ($data['values'] as $value)
            <div
              class="card-light hairline-grid grid gap-3 px-6 py-7 animate-reveal-up timeline-view animate-range-[entry_5%_cover_25%]"
            >
              @if (!empty($value['number']))
                <span
                  class="number-label block text-[var(--accent)]/65"
                  >{{ $value['number'] }}</span
                >
              @endif
              @if (!empty($value['title']))
                <h3
                  class="font-display text-2xl font-medium leading-tight text-[var(--fg)]"
                >
                  {{ $value['title'] }}
                </h3>
              @endif
              @if (!empty($value['description']))
                <p class="text-sm leading-[1.8] text-[var(--fg-muted)]">{{ $value['description'] }}</p>
              @endif
            </div>
          @endforeach
        </div>
      @endif
    </div>

    <div
      class="relative mx-auto grid w-full max-w-2xl content-start gap-8 lg:mx-0 lg:pl-8 animate-reveal-zoom timeline-view animate-range-[entry_5%_cover_30%]"
    >
      <div
        class="pointer-events-none absolute inset-x-2 top-0 h-px bg-[linear-gradient(90deg,transparent,color-mix(in_oklch,var(--accent)_30%,transparent),transparent)]"
        aria-hidden="true"
      ></div>
      <div
        class="quote-panel hairline-grid relative min-h-96 px-10 py-14 md:px-14 md:py-16"
      >
        @if (!empty($data['quote']))
          <blockquote
            class="editorial-quote relative z-10 max-w-full text-left text-[clamp(1.7rem,1.35rem+1vw,2.8rem)] leading-[1.5] text-[var(--fg)]"
          >
            {!! $data['quote'] !!}
          </blockquote>
        @else
          <span
            class="pointer-events-none absolute right-10 top-10 h-28 w-28 rounded-full border border-[var(--accent)]/30"
            aria-hidden="true"
          ></span>
        @endif
      </div>
    </div>
  </div>
</section>
