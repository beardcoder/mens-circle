@push('structured_data')
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

<section class="section section--large testimonials-section" id="stimmen" aria-labelledby="testimonials-title">
    <div class="container">
        <div class="testimonials__header fade-in">
            <p class="eyebrow">Community Stimmen</p>
            <h2 class="section-title testimonials__title" id="testimonials-title">Was <span class="highlight">Teilnehmer</span> sagen</h2>
            <p class="testimonials__subtitle">
                Authentische Einblicke von Männern, die den Kreis erleben
            </p>
        </div>

        <div class="testimonials__grid stagger-children">
            @foreach($testimonials as $testimonial)
                <article class="testimonial-item">
                    <blockquote class="testimonial-item__quote">
                        {{ $testimonial->quote }}
                    </blockquote>

                    @if($testimonial->author_name || $testimonial->role)
                        <div class="testimonial-item__author">
                            @if($testimonial->author_name)
                                <cite class="testimonial-item__name">{{ $testimonial->author_name }}</cite>
                            @endif
                            @if($testimonial->role)
                                <span class="testimonial-item__role">{{ $testimonial->role }}</span>
                            @endif
                        </div>
                    @endif
                </article>
            @endforeach
        </div>
    </div>
</section>
