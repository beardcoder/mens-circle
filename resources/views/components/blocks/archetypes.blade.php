@props (['block'])

@php
    $data = $block->data;
    $items = $data['items'] ?? [];
    $detectIcon = static function (array $item): string {
        $title = mb_strtolower((string) ($item['title'] ?? ''));
        return match (true) {
            str_contains($title, 'krieger') => 'warrior',
            str_contains($title, 'liebhaber') => 'lover',
            str_contains($title, 'zauberer') => 'magician',
            str_contains($title, 'könig'), str_contains($title, 'koenig') => 'king',
            str_contains($title, 'vater') => 'father',
            default => 'neutral',
        };
    };
@endphp

@if (!empty($items) && is_array($items))
  <section
    class="section-y-lg relative isolate overflow-hidden bg-[var(--color-earth-deep)] text-[var(--color-parchment)]"
    id="archetypen"
    aria-labelledby="archetypes-title"
  >
    {{-- Subtle top-warm + bottom-cold radial glows --}}
    <span
      class="pointer-events-none absolute inset-0 -z-10 [background:radial-gradient(ellipse_60%_45%_at_15%_0%,color-mix(in_oklch,var(--color-terracotta)_18%,transparent)_0%,transparent_60%),radial-gradient(ellipse_50%_40%_at_85%_100%,color-mix(in_oklch,var(--color-sage)_14%,transparent)_0%,transparent_55%)]"
      aria-hidden="true"
    ></span>

    <div class="container-page relative">
      {{-- Section header --}}
      <div
        class="mb-20 max-w-3xl animate-reveal-up timeline-view animate-range-[entry_5%_cover_25%]"
      >
        @if (!empty($data['eyebrow']))
          <p class="eyebrow text-[var(--color-terracotta-light)]">{{ $data['eyebrow'] }}</p>
        @endif
        @if (!empty($data['title']))
          <h2
            class="section-title-lg text-[var(--color-parchment)]"
            id="archetypes-title"
          >
            {{ $data['title'] }}
          </h2>
        @endif
        @if (!empty($data['intro']))
          <p class="mt-6 text-lg leading-[1.85] text-[var(--color-sand)]">{{ $data['intro'] }}</p>
        @endif
      </div>

      {{-- Top thread connecting all archetypes --}}
      <div class="relative">
        <span
          class="pointer-events-none absolute inset-x-0 top-0 hidden h-px bg-[linear-gradient(90deg,transparent,color-mix(in_oklch,var(--color-terracotta-light)_55%,transparent),transparent)] sm:block"
          aria-hidden="true"
        ></span>

        <ul
          class="grid grid-cols-1 gap-px bg-[color-mix(in_oklch,var(--color-sand)_15%,transparent)] sm:grid-cols-2 lg:grid-cols-3"
        >
          @foreach ($items as $i => $item)
            @php
                $icon = $detectIcon($item);
                $svgPath = public_path('images/archetypes/' . (in_array($icon, ['warrior', 'lover', 'magician', 'king', 'father'], true) ? $icon : 'neutral') . '.svg');
            @endphp
            <li
              class="group relative isolate flex min-h-[440px] flex-col overflow-hidden bg-[var(--color-earth-deep)] px-8 pb-12 pt-14 transition-colors duration-500 hover:bg-[var(--color-earth-dark)] animate-reveal-up timeline-view animate-range-[entry_5%_cover_25%]"
            >
              {{-- Accent dot at top-left --}}
              <span
                class="absolute -top-1.5 left-8 block h-3 w-3 rounded-full bg-[var(--color-terracotta-light)] shadow-[0_0_16px_color-mix(in_oklch,var(--color-terracotta-light)_60%,transparent)]"
                aria-hidden="true"
              ></span>

              {{-- Background SVG icon — large, low opacity, shifts on hover --}}
              <span
                class="pointer-events-none absolute -right-10 -bottom-10 block h-72 w-72 text-[var(--color-terracotta)]/12 transition-all duration-700 ease-[var(--ease-ambient)] group-hover:scale-105 group-hover:text-[var(--color-terracotta)]/25"
                aria-hidden="true"
              >
                @if (file_exists($svgPath))
                  {!! file_get_contents($svgPath) !!}
                @endif
              </span>

              {{-- Index --}}
              <div class="flex items-baseline gap-3 font-display leading-none">
                <span
                  class="text-[0.7rem] font-medium uppercase tracking-[0.28em] text-[var(--color-terracotta-light)]/80"
                  >Archetyp</span
                >
                <span
                  class="text-2xl font-medium tabular-nums text-[var(--color-parchment)]/90"
                  >{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</span
                >
              </div>

              {{-- Title --}}
              @if (!empty($item['title']))
                <h3
                  class="relative mt-8 font-display text-4xl font-medium leading-[1.05] text-[var(--color-parchment)]"
                >
                  {{ $item['title'] }}
                </h3>
              @endif

              {{-- Description --}}
              @if (!empty($item['description']))
                <p class="relative mt-5 max-w-[28ch] text-[0.95rem] leading-[1.85] text-[var(--color-sand)]/90">{{ $item['description'] }}</p>
              @endif

              {{-- Bottom hairline that grows on hover --}}
              <span
                class="absolute bottom-6 left-8 right-8 h-px origin-left scale-x-0 bg-[var(--color-terracotta-light)]/60 transition-transform duration-500 ease-[var(--ease-settle)] group-hover:scale-x-100"
                aria-hidden="true"
              ></span>
            </li>
          @endforeach
        </ul>
      </div>
    </div>
  </section>
@endif
