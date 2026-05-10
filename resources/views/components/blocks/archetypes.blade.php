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
    class="section-y-lg bg-[var(--bg-alt)]"
    id="archetypen"
    aria-labelledby="archetypes-title"
  >
    <div class="container-page">
      <div x-reveal class="mb-16 max-w-3xl">
        @if (!empty($data['eyebrow']))
          <p class="eyebrow">{{ $data['eyebrow'] }}</p>
        @endif
        @if (!empty($data['title']))
          <h2 class="section-title-lg" id="archetypes-title">
            {{ $data['title'] }}
          </h2>
        @endif
        @if (!empty($data['intro']))
          <p class="mt-6 text-lg leading-[1.85] text-[var(--fg-muted)]">{{ $data['intro'] }}</p>
        @endif
      </div>

      <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($items as $item)
          @php
              $icon = $detectIcon($item);
              $svgPath = public_path('images/archetypes/' . (in_array($icon, ['warrior', 'lover', 'magician', 'king', 'father'], true) ? $icon : 'neutral') . '.svg');
          @endphp
          <article
            x-reveal
            class="group relative isolate flex min-h-[420px] flex-col justify-end overflow-hidden rounded-3xl bg-gradient-to-br from-[var(--color-earth-deep)] via-[var(--color-earth-dark)] to-[var(--color-earth-deep)] p-10 text-[var(--color-parchment)] shadow-[0_8px_32px_color-mix(in_oklch,var(--color-ink)_18%,transparent)] transition-all duration-500 hover:-translate-y-1.5 hover:shadow-[0_20px_40px_-10px_color-mix(in_oklch,var(--color-ink)_25%,transparent)]"
          >
            <span
              class="pointer-events-none absolute -right-12 -bottom-12 block h-72 w-72 text-[var(--color-terracotta)]/15 transition-all duration-700 group-hover:scale-110 group-hover:text-[var(--color-terracotta)]/25"
              aria-hidden="true"
            >
              @if (file_exists($svgPath))
                {!! file_get_contents($svgPath) !!}
              @endif
            </span>
            <span
              class="pointer-events-none absolute inset-0 -z-10 [background:radial-gradient(circle_at_30%_30%,color-mix(in_oklch,var(--accent)_20%,transparent)_0%,transparent_60%)] opacity-0 transition-opacity duration-500 group-hover:opacity-100"
              aria-hidden="true"
            ></span>
            <div class="relative">
              @if (!empty($item['title']))
                <h3 class="font-display text-3xl font-medium leading-tight">
                  {{ $item['title'] }}
                </h3>
              @endif
              @if (!empty($item['description']))
                <p class="mt-4 text-sm leading-[1.85] text-[var(--color-sand)]">{{ $item['description'] }}</p>
              @endif
            </div>
          </article>
        @endforeach
      </div>
    </div>
  </section>
@endif
