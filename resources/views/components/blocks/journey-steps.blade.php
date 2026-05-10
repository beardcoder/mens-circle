@props (['block'])

@php
    $data = $block->data;
    $steps = $data['steps'] ?? [];
    $count = count($steps);
    $colClass = match (min($count, 4)) {
        4 => 'lg:grid-cols-4',
        3 => 'lg:grid-cols-3',
        2 => 'lg:grid-cols-2',
        default => 'lg:grid-cols-1',
    };
@endphp

@if (!empty($steps) && is_array($steps))
  @push ('structured_data')
    <script type="application/ld+json">
      {
          "@@context": "https://schema.org",
          "@@type": "HowTo",
          "name": "{{ strip_tags($data['title'] ?? 'Deine Reise zum Männerkreis') }}",
          "description": "{{ $data['subtitle'] ?? 'Wie du Teil des Männerkreis wirst' }}",
          "step": [
              @foreach($steps as $step)
              {
                  "@@type": "HowToStep",
                  "position": {{ $loop->iteration }},
                  "name": "{{ e($step['title'] ?? '') }}",
                  "text": "{{ e($step['description'] ?? '') }}"
              }@if(!$loop->last),@endif
              @endforeach
          ]
      }
    </script>
  @endpush
@endif

<section
  class="section-y-lg relative isolate overflow-hidden bg-[var(--color-earth-deep)] text-[var(--color-parchment)]"
  id="reise"
  aria-labelledby="journey-title"
>
  {{-- Subtle bottom warm glow + ambient circle --}}
  <span
    class="pointer-events-none absolute inset-0 -z-10 [background:radial-gradient(ellipse_80%_60%_at_50%_100%,color-mix(in_oklch,var(--accent)_11%,transparent)_0%,transparent_56%)]"
    aria-hidden="true"
  ></span>
  <span
    class="pointer-events-none absolute -bottom-[20vw] -right-[20vw] block h-[60vw] w-[60vw] rounded-full border border-[var(--color-sand)]/10 animate-breathe [animation-duration:40s]"
    aria-hidden="true"
  ></span>

  <div class="container-page relative">
    {{-- Section header --}}
    <div
      class="section-header max-w-4xl animate-reveal-up timeline-view animate-range-[entry_5%_cover_25%]"
    >
      @if (!empty($data['eyebrow']))
        <p class="eyebrow text-[var(--color-terracotta-light)]">{{ $data['eyebrow'] }}</p>
      @endif
      @if (!empty($data['title']))
        <h2
          class="section-title-lg split-title text-[var(--color-parchment)]"
          id="journey-title"
        >
          {!! $data['title'] !!}
        </h2>
      @endif
      @if (!empty($data['subtitle']))
        <p class="section-intro max-w-[58ch] text-[var(--color-sand)]/85">{{ $data['subtitle'] }}</p>
      @endif
    </div>

    {{-- Editorial timeline --}}
    <div class="relative">
      <span
        class="pointer-events-none absolute left-6 top-0 bottom-0 w-px bg-[linear-gradient(180deg,transparent,color-mix(in_oklch,var(--color-terracotta-light)_46%,transparent),transparent)] md:hidden"
        aria-hidden="true"
      ></span>
      <span
        class="pointer-events-none absolute inset-x-0 top-0 hidden h-px bg-[linear-gradient(90deg,transparent,color-mix(in_oklch,var(--color-terracotta-light)_48%,transparent),transparent)] lg:block"
        aria-hidden="true"
      ></span>

      <ol
        class="grid grid-cols-1 gap-y-8 md:grid-cols-2 md:gap-x-6 lg:gap-y-0 {{ $colClass }}"
      >
        @foreach ($steps as $step)
          <li
            class="group card-dark relative flex flex-col gap-4 px-7 py-8 md:py-10 transition-colors duration-500 hover:bg-[color-mix(in_oklch,var(--color-earth-dark)_88%,transparent)] lg:rounded-none lg:border-0 lg:bg-transparent lg:px-8 lg:pt-14 lg:pb-10 lg:[&:not(:first-child)]:border-l lg:[&:not(:first-child)]:border-[var(--color-sand)]/15 animate-reveal-up timeline-view animate-range-[entry_5%_cover_25%]"
          >
            {{-- Accent dot at top --}}
            <span
              class="absolute top-8 -left-[1.45rem] block h-2.5 w-2.5 rounded-full bg-[var(--color-terracotta-light)] shadow-[0_0_14px_color-mix(in_oklch,var(--color-terracotta-light)_45%,transparent)] md:left-7 md:top-8 lg:-top-1.5 lg:left-8"
              aria-hidden="true"
            ></span>

            @if (!empty($step['number']))
              <div class="flex items-baseline gap-3 font-display leading-none">
                <span
                  class="text-xs font-medium uppercase tracking-[0.23em] text-[var(--color-terracotta-light)]/80"
                  >Schritt</span
                >
                <span
                  class="number-label text-[var(--color-parchment)]/82"
                  >{{ str_pad((string) $step['number'], 2, '0', STR_PAD_LEFT) }}</span
                >
              </div>
            @endif

            @if (!empty($step['title']))
              <h3
                class="font-display text-[clamp(1.5rem,1rem+1vw,2rem)] font-medium leading-tight text-[var(--color-parchment)]"
              >
                {{ $step['title'] }}
              </h3>
            @endif

            @if (!empty($step['description']))
              <p class="text-[0.95rem] leading-[1.82] text-[var(--color-sand)]/90">{{ $step['description'] }}</p>
            @endif
          </li>
        @endforeach
      </ol>
    </div>
  </div>
</section>
