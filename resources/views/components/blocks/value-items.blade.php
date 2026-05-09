@props (['block'])

@php
    $data = $block->data;
@endphp

<section class="section-y bg-[var(--bg-alt)]">
  <div class="container-page">
    <div x-reveal class="mb-12 max-w-3xl">
      @if (!empty($data['eyebrow']))
        <p class="eyebrow">{{ $data['eyebrow'] }}</p>
      @endif
      @if (!empty($data['title']))
        <h2 class="section-title">{{ $data['title'] }}</h2>
      @endif
    </div>

    @if (!empty($data['items']) && is_array($data['items']))
      <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($data['items'] as $item)
          <div
            x-reveal
            class="flex flex-col gap-3 rounded-2xl border border-[var(--border)] bg-[var(--bg)] p-8 transition-transform hover:-translate-y-1"
          >
            @if (!empty($item['number']))
              <span
                class="font-display text-5xl font-semibold text-[var(--accent)]"
                >{{ $item['number'] }}</span
              >
            @endif
            @if (!empty($item['title']))
              <h3 class="font-display text-xl font-medium">
                {{ $item['title'] }}
              </h3>
            @endif
            @if (!empty($item['description']))
              <p class="text-sm text-[var(--fg-muted)] leading-relaxed">{{ $item['description'] }}</p>
            @endif
          </div>
        @endforeach
      </div>
    @endif
  </div>
</section>
