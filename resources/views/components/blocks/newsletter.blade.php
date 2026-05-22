@props (['block'])

@php
    $data = $block->data;
    $anchor = $data['anchor'] ?? 'newsletter';
@endphp

<section
  class="section newsletter-section"
  id="{{ $anchor }}"
  aria-labelledby="newsletter-title"
>
  <div class="container">
    <div class="newsletter__layout">
      <div class="newsletter__content">
        @if (!empty($data['eyebrow']))
          <p class="eyebrow eyebrow--secondary">{{ $data['eyebrow'] }}</p>
        @endif

        @if (!empty($data['title']))
          <h2 class="section-title newsletter__title" id="newsletter-title">
            {!! $data['title'] !!}
          </h2>
        @endif

        @if (!empty($data['text']))
          <p class="newsletter__text">{{ $data['text'] }}</p>
        @endif
      </div>

      <div class="newsletter__form-wrapper">
        <form
          id="newsletterForm"
          class="newsletter__form"
          aria-label="Newsletter-Anmeldung"
          x-data="newsletterForm"
        >
          <label for="newsletter-email" class="sr-only">E-Mail-Adresse</label>
          <input
            type="email"
            id="newsletter-email"
            name="email"
            placeholder="Deine E-Mail-Adresse"
            required
            class="newsletter__input"
            autocomplete="email"
            inputmode="email"
          />
          <button type="submit" class="btn btn--primary">Anmelden</button>
          <div id="newsletterMessage" role="status" aria-live="polite"></div>
        </form>
      </div>
    </div>
  </div>
</section>
