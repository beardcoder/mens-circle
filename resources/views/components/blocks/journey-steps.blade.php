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
  class="section-y-lg relative isolate overflow-hidden bg-gradient-to-b from-[var(--color-earth-deep)] to-[var(--color-earth-dark)] text-[var(--color-parchment)]"
  id="reise"
  aria-labelledby="journey-title"
>
  <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
    <div
      class="absolute inset-0 [background:radial-gradient(ellipse_60%_50%_at_80%_30%,color-mix(in_oklch,var(--accent)_15%,transparent)_0%,transparent_50%)]"
    ></div>
    <span
      class="absolute -top-[10vw] -right-[10vw] block h-[40vw] w-[40vw] rounded-full border border-[var(--color-sand)]/20 animate-breathe [animation-duration:24s]"
    ></span>
    <span
      class="absolute -bottom-[20vw] -left-[20vw] block h-[60vw] w-[60vw] rounded-full border border-[var(--color-sand)]/15 animate-breathe [animation-delay:-8s] [animation-duration:32s]"
    ></span>
  </div>

  <div class="container-page relative">
    <div x-reveal class="mb-20 max-w-2xl">
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
        <p class="mt-6 text-lg leading-[1.85] text-[var(--color-sand)]">{{ $data['subtitle'] }}</p>
      @endif
    </div>

    @if (!empty($steps))
      <ol
        class="grid gap-px bg-white/5 lg:grid-cols-3 lg:rounded-3xl lg:overflow-hidden"
      >
        @foreach ($steps as $step)
          <li
            x-reveal
            class="group relative flex flex-col gap-4 bg-[color-mix(in_oklch,var(--color-earth-deep)_92%,transparent)] p-10 backdrop-blur-sm transition-colors hover:bg-[color-mix(in_oklch,var(--color-earth-dark)_85%,transparent)]"
          >
            @if (!empty($step['number']))
              <div
                class="font-display text-7xl font-medium leading-none text-[var(--color-terracotta-light)]/40 transition-colors group-hover:text-[var(--color-terracotta-light)]/60"
                aria-hidden="true"
              >
                {{ str_pad((string) $step['number'], 2, '0', STR_PAD_LEFT) }}
              </div>
            @endif
            @if (!empty($step['title']))
              <h3 class="font-display text-2xl font-medium">
                {{ $step['title'] }}
              </h3>
            @endif
            @if (!empty($step['description']))
              <p class="text-sm leading-[1.85] text-[var(--color-sand)]">{{ $step['description'] }}</p>
            @endif
          </li>
        @endforeach
      </ol>
    @endif
  </div>
</section>
