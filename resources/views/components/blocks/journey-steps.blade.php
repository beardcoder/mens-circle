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
    class="pointer-events-none absolute inset-0 -z-10 [background:radial-gradient(ellipse_80%_60%_at_50%_100%,color-mix(in_oklch,var(--accent)_16%,transparent)_0%,transparent_50%)]"
    aria-hidden="true"
  ></span>
  <span
    class="pointer-events-none absolute -bottom-[20vw] -right-[20vw] block h-[60vw] w-[60vw] rounded-full border border-[var(--color-sand)]/10 animate-breathe [animation-duration:32s]"
    aria-hidden="true"
  ></span>

  <div class="container-page relative">
    {{-- Section header --}}
    <div x-reveal class="mb-20 max-w-3xl">
      @if (!empty($data['eyebrow']))
        <p class="eyebrow text-[var(--color-terracotta-light)]">{{ $data['eyebrow'] }}</p>
      @endif
      @if (!empty($data['title']))
        <h2
          class="section-title-lg text-[var(--color-parchment)]"
          id="journey-title"
        >
          {!! $data['title'] !!}
        </h2>
      @endif
      @if (!empty($data['subtitle']))
        <p class="mt-6 font-display text-2xl italic leading-snug text-[var(--color-sand)]/80">{{ $data['subtitle'] }}</p>
      @endif
    </div>

    {{-- Horizontal timeline thread (desktop) --}}
    <div class="relative">
      <span
        class="pointer-events-none absolute inset-x-0 top-0 hidden h-px bg-[linear-gradient(90deg,transparent,color-mix(in_oklch,var(--color-terracotta-light)_60%,transparent),transparent)] lg:block"
        aria-hidden="true"
      ></span>

      <ol
        class="grid grid-cols-1 gap-y-12 sm:grid-cols-2 sm:gap-x-6 lg:gap-y-0 {{ $colClass }}"
      >
        @foreach ($steps as $step)
          <li
            x-reveal
            class="group relative flex flex-col gap-5 px-6 pt-12 transition-colors duration-500 hover:bg-[color-mix(in_oklch,var(--color-sand)_4%,transparent)] lg:px-8 lg:pt-14 lg:[&:not(:first-child)]:border-l lg:[&:not(:first-child)]:border-[var(--color-sand)]/15"
          >
            {{-- Accent dot at top --}}
            <span
              class="absolute -top-1.5 left-6 block h-3 w-3 rounded-full bg-[var(--color-terracotta-light)] shadow-[0_0_16px_color-mix(in_oklch,var(--color-terracotta-light)_70%,transparent)] lg:left-8"
              aria-hidden="true"
            ></span>

            @if (!empty($step['number']))
              <div class="flex items-baseline gap-3 font-display leading-none">
                <span
                  class="text-xs font-medium uppercase tracking-[0.25em] text-[var(--color-terracotta-light)]/80"
                  >Schritt</span
                >
                <span
                  class="text-6xl font-medium text-[var(--color-parchment)]"
                  >{{ str_pad((string) $step['number'], 2, '0', STR_PAD_LEFT) }}</span
                >
              </div>
            @endif

            @if (!empty($step['title']))
              <h3
                class="font-display text-2xl font-medium leading-tight text-[var(--color-parchment)]"
              >
                {{ $step['title'] }}
              </h3>
            @endif

            @if (!empty($step['description']))
              <p class="text-[0.95rem] leading-[1.85] text-[var(--color-sand)]/90">{{ $step['description'] }}</p>
            @endif
          </li>
        @endforeach
      </ol>
    </div>
  </div>
</section>
