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
    <div x-reveal class="mb-12 max-w-3xl">
      <p class="eyebrow">Community Stimmen</p>
      <h2 class="section-title-lg" id="testimonials-title">
        Was <span class="text-italic">Teilnehmer</span> sagen
      </h2>
      <p class="mt-4 text-lg text-[var(--fg-muted)]">Authentische Einblicke von Männern, die den Kreis erleben</p>
    </div>

    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
      @foreach ($testimonials as $testimonial)
        <article
          x-reveal
          class="rounded-2xl border border-[var(--border)] bg-[var(--bg-alt)] p-8 transition-transform hover:-translate-y-1"
        >
          <blockquote
            class="font-display text-lg italic leading-relaxed text-[var(--fg)]"
          >
            »{{ $testimonial->quote }}«
          </blockquote>
          @if ($testimonial->author_name || $testimonial->role)
            <div class="mt-6 flex flex-col gap-1 text-sm">
              @if ($testimonial->author_name)
                <cite
                  class="not-italic font-medium text-[var(--fg)]"
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
