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

<section class="section-y-lg" id="stimmen" aria-labelledby="testimonials-title">
  <div class="container-page">
    <div x-reveal class="mb-16 max-w-3xl">
      <p class="eyebrow">Community Stimmen</p>
      <h2 class="section-title-lg" id="testimonials-title">
        Was <span class="text-italic">Teilnehmer</span> sagen
      </h2>
      <p class="mt-6 text-lg leading-[1.85] text-[var(--fg-muted)]">Authentische Einblicke von Männern, die den Kreis erleben</p>
    </div>

    <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
      @foreach ($testimonials as $testimonial)
        <article
          x-reveal
          class="group relative flex flex-col gap-6 border border-[var(--border)] bg-[var(--bg-alt)] p-10 transition-colors duration-500 hover:bg-[var(--bg)]"
        >
          <span
            class="font-display text-6xl leading-none text-[var(--accent)]/50"
            aria-hidden="true"
            >»</span
          >
          <blockquote
            class="font-display text-xl italic leading-[1.5] text-[var(--fg)]"
          >
            {{ $testimonial->quote }}
          </blockquote>
          @if ($testimonial->author_name || $testimonial->role)
            <div
              class="mt-auto flex flex-col gap-1 border-t border-[var(--border)] pt-5 text-sm"
            >
              @if ($testimonial->author_name)
                <cite
                  class="font-display text-base font-medium not-italic text-[var(--fg)]"
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
