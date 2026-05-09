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
    class="section-y-lg"
    id="archetypen"
    aria-labelledby="archetypes-title"
  >
    <div class="container-page">
      <div x-reveal class="mb-12 max-w-3xl">
        @if (!empty($data['eyebrow']))
          <p class="eyebrow">{{ $data['eyebrow'] }}</p>
        @endif
        @if (!empty($data['title']))
          <h2 class="section-title-lg" id="archetypes-title">
            {{ $data['title'] }}
          </h2>
        @endif
        @if (!empty($data['intro']))
          <p class="mt-4 text-lg text-[var(--fg-muted)]">{{ $data['intro'] }}</p>
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
            class="group relative isolate overflow-hidden rounded-2xl bg-[var(--color-earth-deep)] p-8 text-[var(--color-parchment)] transition-transform hover:-translate-y-1"
          >
            <div
              class="pointer-events-none absolute -right-6 -bottom-6 h-48 w-48 text-[var(--color-terracotta)]/15 transition-transform duration-500 group-hover:scale-110"
              aria-hidden="true"
            >
              @if (file_exists($svgPath))
                {!! file_get_contents($svgPath) !!}
              @endif
            </div>
            <div class="relative">
              @if (!empty($item['title']))
                <h3 class="font-display text-2xl font-medium">
                  {{ $item['title'] }}
                </h3>
              @endif
              @if (!empty($item['description']))
                <p class="mt-3 text-sm text-[var(--color-sand)] leading-relaxed">{{ $item['description'] }}</p>
              @endif
            </div>
          </article>
        @endforeach
      </div>
    </div>
  </section>
@endif
