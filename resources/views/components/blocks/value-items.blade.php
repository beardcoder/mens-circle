@props (['block'])

@php
    $data = $block->data;
@endphp

<section class="section-y editorial-light">
  <div class="container-page">
    <div
      class="section-header animate-reveal-up timeline-view animate-range-[entry_5%_cover_25%]"
    >
      @if (!empty($data['eyebrow']))
        <p class="eyebrow">{{ $data['eyebrow'] }}</p>
      @endif
      @if (!empty($data['title']))
        <h2 class="section-title split-title max-w-[16ch]">
          {{ $data['title'] }}
        </h2>
      @endif
    </div>

    @if (!empty($data['items']) && is_array($data['items']))
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($data['items'] as $item)
          <div
            class="card-light hairline-grid group relative flex h-full flex-col gap-4 p-8 md:p-10 transition-colors duration-500 hover:bg-[color-mix(in_oklch,var(--bg)_72%,var(--bg-alt))] animate-reveal-up timeline-view animate-range-[entry_5%_cover_25%]"
          >
            @if (!empty($item['number']))
              <span
                class="number-label text-[var(--accent)]/55 transition-colors group-hover:text-[var(--accent)]/65"
                >{{ $item['number'] }}</span
              >
            @endif
            @if (!empty($item['title']))
              <h3
                class="font-display text-[clamp(1.6rem,1.2rem+1vw,2.2rem)] font-medium leading-[1.08]"
              >
                {{ $item['title'] }}
              </h3>
            @endif
            @if (!empty($item['description']))
              <p class="text-[0.96rem] leading-[1.84] text-[var(--fg-muted)]">{{ $item['description'] }}</p>
            @endif
          </div>
        @endforeach
      </div>
    @endif
  </div>
</section>
