@props (['block'])

@php
    $data = $block->data;
    $steps = $data['steps'] ?? [];
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
  class="section-y-lg bg-[var(--bg-deep)] text-[var(--color-parchment)]"
  id="reise"
  aria-labelledby="journey-title"
>
  <div class="container-page">
    <div x-reveal class="mb-16 max-w-2xl">
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
        <p class="mt-4 text-lg text-[var(--color-sand)]">{{ $data['subtitle'] }}</p>
      @endif
    </div>

    @if (!empty($steps))
      <ol class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
        @foreach ($steps as $step)
          <li
            x-reveal
            class="relative rounded-2xl border border-white/10 bg-white/5 p-8 backdrop-blur-sm"
          >
            @if (!empty($step['number']))
              <div
                class="font-display text-6xl font-semibold text-[var(--color-terracotta-light)]/40"
                aria-hidden="true"
              >
                {{ $step['number'] }}
              </div>
            @endif
            @if (!empty($step['title']))
              <h3 class="mt-2 font-display text-xl font-medium">
                {{ $step['title'] }}
              </h3>
            @endif
            @if (!empty($step['description']))
              <p class="mt-3 text-sm text-[var(--color-sand)] leading-relaxed">{{ $step['description'] }}</p>
            @endif
          </li>
        @endforeach
      </ol>
    @endif
  </div>
</section>
