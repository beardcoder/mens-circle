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

    <div class="grid items-stretch gap-4 md:grid-cols-2 lg:grid-cols-3">
      @foreach ($testimonials as $testimonial)
        <article
          class="card-light editorial-panel group relative flex h-full flex-col gap-6 p-8 md:p-9 transition-colors duration-500 hover:bg-[color-mix(in_oklch,var(--bg)_80%,var(--bg-alt))] animate-reveal-up timeline-view animate-range-[entry_5%_cover_25%]"
        >
          <span
            class="font-display text-7xl leading-none text-[var(--accent)]/28"
            aria-hidden="true"
            >»</span
          >
          <blockquote
            class="font-display text-[clamp(1.15rem,0.95rem+0.7vw,1.55rem)] italic leading-[1.65] text-[var(--fg)]"
          >
            {{ $testimonial->quote }}
          </blockquote>
          @if ($testimonial->author_name || $testimonial->role)
            <div
              class="mt-auto grid gap-1 border-t border-[color-mix(in_oklch,var(--border)_80%,transparent)] pt-5 text-sm"
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
