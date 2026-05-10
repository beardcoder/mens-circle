@push ('structured_data')
  <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "ItemList",
        "name": "Teilnehmerstimmen",
        "description": "Authentische Einblicke von Männern, die den Männerkreis erleben",
        "numberOfItems": {{ $testimonials->count() }},
        "itemListElement": [
            @foreach($testimonials as $index => $testimonial)
            {
                "@@type": "Review",
                "position": {{ $index + 1 }},
                "reviewBody": "{{ e($testimonial->quote) }}",
                "author": {
                    "@@type": "Person",
                    "name": "{{ e($testimonial->author_name ?: 'Anonymer Teilnehmer') }}"
                },
                "itemReviewed": {
                    "@@type": "Organization",
                    "@@id": "{{ url('/') }}#organization"
                }
            }@if(!$loop->last),@endif
            @endforeach
        ]
    }
  </script>
@endpush

<section
  class="section-y-lg editorial-light"
  id="stimmen"
  aria-labelledby="testimonials-title"
>
  <div class="container-page">
    <div
      class="section-header animate-reveal-up timeline-view animate-range-[entry_5%_cover_25%]"
    >
      <p class="eyebrow">Community Stimmen</p>
      <h2
        class="section-title-lg split-title max-w-[16ch]"
        id="testimonials-title"
      >
        Was <span class="text-italic">Teilnehmer</span> sagen
      </h2>
      <p class="section-intro">Authentische Einblicke von Männern, die den Kreis erleben</p>
    </div>

    <div class="grid items-stretch gap-5 md:grid-cols-2 lg:grid-cols-3">
      @foreach ($testimonials as $testimonial)
        <article
          class="testimonial-panel group relative flex h-full flex-col gap-8 px-8 py-10 md:px-9 md:py-11 transition-colors duration-500 hover:bg-[color-mix(in_oklch,var(--bg)_86%,var(--bg-alt))] animate-reveal-up timeline-view animate-range-[entry_5%_cover_25%]"
        >
          <span
            class="pointer-events-none absolute right-8 top-6 font-display text-[clamp(4.4rem,3vw+2rem,6.8rem)] leading-none text-[var(--accent)]/14"
            aria-hidden="true"
            >»</span
          >
          <blockquote
            class="relative z-10 font-display text-[clamp(1.33rem,1rem+0.8vw,1.95rem)] italic leading-[1.64] text-[var(--fg)]"
          >
            {{ $testimonial->quote }}
          </blockquote>
          @if ($testimonial->author_name || $testimonial->role)
            <div
              class="mt-auto grid gap-1 border-t border-[color-mix(in_oklch,var(--border)_74%,transparent)] pt-6 text-sm"
            >
              @if ($testimonial->author_name)
                <cite
                  class="font-display text-[1.08rem] font-medium not-italic text-[var(--fg)]"
                  >{{ $testimonial->author_name }}</cite
                >
              @endif
              @if ($testimonial->role)
                <span
                  class="text-[var(--fg-muted)]"
                  >{{ $testimonial->role }}</span
                >
              @endif
            </div>
          @endif
        </article>
      @endforeach
    </div>
  </div>
</section>
