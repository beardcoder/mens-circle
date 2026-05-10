@props (['block'])

@php
    $data = $block->data;
@endphp

<section class="section-y bg-[var(--bg-alt)]">
  <div class="container-page">
    <div x-reveal class="mb-16 max-w-3xl">
      @if (!empty($data['eyebrow']))
        <p class="eyebrow">{{ $data['eyebrow'] }}</p>
      @endif
      @if (!empty($data['title']))
        <h2 class="section-title">{{ $data['title'] }}</h2>
      @endif
    </div>

    @if (!empty($data['items']) && is_array($data['items']))
      <div
        class="grid gap-px bg-[var(--border)] sm:grid-cols-2 lg:grid-cols-3 lg:rounded-3xl lg:overflow-hidden"
      >
        @foreach ($data['items'] as $item)
          <div
            x-reveal
            class="group relative flex flex-col gap-4 bg-[var(--bg)] p-10 transition-colors duration-500 hover:bg-[var(--bg-alt)]"
          >
            @if (!empty($item['number']))
              <span
                class="font-display text-6xl font-medium leading-none text-[var(--accent)]/80 transition-colors group-hover:text-[var(--accent)]"
                >{{ $item['number'] }}</span
              >
            @endif
            @if (!empty($item['title']))
              <h3 class="font-display text-2xl font-medium">
                {{ $item['title'] }}
              </h3>
            @endif
            @if (!empty($item['description']))
              <p class="text-sm leading-relaxed text-[var(--fg-muted)]">{{ $item['description'] }}</p>
            @endif
          </div>
        @endforeach
      </div>
    @endif
  </div>
</section>
