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
                <div class="testimonial-card">
                    <div class="testimonial-card__quote-icon">
                        <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10 16C10 13.7909 8.20914 12 6 12V8C10.4183 8 14 11.5817 14 16V22C14 23.1046 13.1046 24 12 24H8C6.89543 24 6 23.1046 6 22V18C6 16.8954 6.89543 16 8 16H10ZM24 16C24 13.7909 22.2091 12 20 12V8C24.4183 8 28 11.5817 28 16V22C28 23.1046 27.1046 24 26 24H22C20.8954 24 20 23.1046 20 22V18C20 16.8954 20.8954 16 22 16H24Z" fill="currentColor"/>
                        </svg>
                    </div>

                    <blockquote class="testimonial-card__quote">
                        {{ $testimonial->quote }}
                    </blockquote>

                    @if($testimonial->author_name || $testimonial->role)
                        <div class="testimonial-card__author">
                            @if($testimonial->author_name)
                                <cite class="testimonial-card__name">{{ $testimonial->author_name }}</cite>
                            @endif
                            @if($testimonial->role)
                                <span class="testimonial-card__role">{{ $testimonial->role }}</span>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>
