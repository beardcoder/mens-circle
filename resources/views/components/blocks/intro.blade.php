@props (['block'])

@php
    $data = $block->data;
@endphp

<section
  class="section-y-lg bg-[var(--bg-alt)]"
  id="ueber"
  aria-labelledby="intro-title"
>
  <div
    class="container-page grid gap-14 lg:grid-cols-[1.08fr_0.92fr] lg:items-start"
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
      class="relative mx-auto grid w-full max-w-xl content-start gap-8 lg:mx-0 lg:pl-10 animate-reveal-zoom timeline-view animate-range-[entry_5%_cover_30%]"
    >
      <div
        class="pointer-events-none absolute inset-x-0 top-0 h-px bg-[linear-gradient(90deg,transparent,color-mix(in_oklch,var(--accent)_40%,transparent),transparent)]"
        aria-hidden="true"
      ></div>
      <div
        class="editorial-panel card-light relative min-h-80 rounded-[1.5rem] px-8 py-10 md:px-10"
      >
        @if (!empty($data['quote']))
          <p class="editorial-quote relative z-10 max-w-full text-left text-[var(--fg)]">{!! $data['quote'] !!}</p>
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
