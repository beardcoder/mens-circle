<section class="section newsletter-section" id="newsletter">
    <div class="container">
        <div class="newsletter__layout fade-in">
            <div class="newsletter__content">
                @if(!empty($block['data']['eyebrow']))
                    <p class="newsletter__eyebrow">{{ $block['data']['eyebrow'] }}</p>
                @endif

                @if(!empty($block['data']['title']))
                    <h2 class="newsletter__title">{!! $block['data']['title'] !!}</h2>
                @endif

                @if(!empty($block['data']['text']))
                    <p class="newsletter__text">{{ $block['data']['text'] }}</p>
                @endif
            </div>

            <div class="newsletter__form-wrapper">
                <form id="newsletterForm" class="newsletter__form">
                    <input
                        type="email"
                        name="email"
                        placeholder="Deine E-Mail-Adresse"
                        required
                        class="newsletter__input"
                        aria-label="E-Mail-Adresse"
                    >
                    <button type="submit" class="btn btn--primary" data-m:click="action=form_submit;element=button;target=newsletter_subscription;location=newsletter_section">
                        Anmelden
                    </button>
                    <div id="newsletterMessage"></div>
                </form>
            </div>
        </div>
    </div>
</section>
