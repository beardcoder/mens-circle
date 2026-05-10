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

<section class="section-y-lg" id="moderator">
  <div
    class="container-page grid gap-12 lg:grid-cols-[1fr_1.08fr] lg:items-start"
  >
    <div
      class="relative mx-auto w-full max-w-xl animate-reveal-up timeline-view animate-range-[entry_5%_cover_25%] lg:mx-0"
    >
      <span
        class="pointer-events-none absolute -bottom-6 -right-6 h-28 w-28 rounded-full border border-[var(--accent)]/25 animate-breathe [animation-duration:20s]"
        aria-hidden="true"
      ></span>
      <div
        class="card-light editorial-panel relative aspect-[5/6] h-full w-full overflow-hidden rounded-[1.6rem] bg-[var(--bg-alt)]"
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

    <div
      class="animate-reveal-up timeline-view animate-range-[entry_5%_cover_25%]"
    >
      @if (!empty($data['eyebrow']))
        <p class="eyebrow">{{ $data['eyebrow'] }}</p>
      @endif
      @if (!empty($data['name']))
        <h2 class="section-title-lg split-title max-w-[15ch]">
          {!! $data['name'] !!}
        </h2>
      @endif
      @if (!empty($data['bio']))
        <div
          class="prose-block mt-7 text-lg leading-[1.9] text-[var(--fg-muted)]"
        >
          {!! $data['bio'] !!}
        </div>
      @endif
      @if (!empty($data['quote']))
        <blockquote
          class="card-light editorial-panel mt-10 rounded-[1.2rem] px-7 py-6 font-display text-[clamp(1.3rem,1rem+0.9vw,1.9rem)] italic leading-[1.6] text-[var(--fg)]"
        >
          <p class="editorial-quote max-w-full before:-top-3 before:left-0">»{{ $data['quote'] }}«</p>
        </blockquote>
      @endif
    </div>
  </div>
</section>
