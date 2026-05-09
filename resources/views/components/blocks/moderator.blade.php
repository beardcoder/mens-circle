@props ([
    'block',
    'page' => null,
])

@php
    $data = $block->data;
    $media = $block->getFieldMedia('photo');
    if ($media) {
        $media->name = strip_tags($data['name'] ?? 'Moderator');
    }
@endphp

<section class="section-y" id="moderator">
  <div class="container-page grid gap-12 md:grid-cols-2 md:items-center">
    <div x-reveal class="relative aspect-[4/5] w-full max-w-md mx-auto md:mx-0">
      <div
        class="absolute -inset-4 rounded-3xl bg-gradient-to-br from-[var(--accent)]/20 to-transparent blur-xl"
      ></div>
      <div
        class="relative h-full w-full overflow-hidden rounded-2xl bg-[var(--bg-alt)]"
      >
        @if ($media)
          {{ $media->img()->attributes([
              'class' => 'h-full w-full object-cover',
              'loading' => 'lazy',
              'decoding' => 'async',
          ]) }}
        @else
          <div
            class="grid h-full w-full place-items-center text-[var(--fg-muted)]"
          >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-24 w-24" aria-hidden="true">
              <circle cx="12" cy="8" r="4" />
              <path d="M4 20c0-4 4-6 8-6s8 2 8 6" />
            </svg>
          </div>
        @endif
      </div>
    </div>

    <div x-reveal>
      @if (!empty($data['eyebrow']))
        <p class="eyebrow">{{ $data['eyebrow'] }}</p>
      @endif
      @if (!empty($data['name']))
        <h2 class="section-title-lg">{!! $data['name'] !!}</h2>
      @endif
      @if (!empty($data['bio']))
        <div class="prose-block mt-6 text-[var(--fg-muted)]">
          {!! $data['bio'] !!}
        </div>
      @endif
      @if (!empty($data['quote']))
        <blockquote
          class="mt-8 border-l-2 border-[var(--accent)] pl-6 font-display text-xl italic text-[var(--fg)]"
        >
          <p>{{ $data['quote'] }}</p>
        </blockquote>
      @endif
    </div>
  </div>
</section>
