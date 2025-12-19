<section class="section newsletter-section">
    <div class="container container--narrow">
        <div class="newsletter-card fade-in">
            @if(!empty($block['eyebrow']))
                <p class="section__eyebrow">{{ $block['eyebrow'] }}</p>
            @endif

            @if(!empty($block['title']))
                <h2>{{ $block['title'] }}</h2>
            @endif

            @if(!empty($block['text']))
                <p>{{ $block['text'] }}</p>
            @endif

            <form id="newsletterForm" class="newsletter-form">
                <div class="form__group form__group--inline">
                    <input
                        type="email"
                        name="email"
                        placeholder="Deine E-Mail-Adresse"
                        required
                        aria-label="E-Mail-Adresse"
                    >
                    <button type="submit" class="btn btn--primary">
                        Anmelden
                    </button>
                </div>
                <div id="newsletterMessage"></div>
            </form>

            <p class="newsletter-card__privacy">
                Mit der Anmeldung akzeptierst du unsere
                <a href="{{ route('datenschutz') }}">Datenschutzerklärung</a>.
            </p>
        </div>
    </div>
</section>
