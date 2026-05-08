@props (['block'])

@php
    $data = $block->data;
@endphp

<section
  class="relative overflow-hidden py-xl text-text-on-dark bg-bg-section-alt newsletter-section"
  id="newsletter"
  aria-labelledby="newsletter-title"
>
  <div class="w-full max-w-container px-md mx-auto">
    <div class="relative z-10 grid grid-cols-2 gap-xl items-center max-[800px]:grid-cols-1 max-[800px]:gap-lg">
      <div class="animate-fade-right">
        @if (!empty($data['eyebrow']))
          <p class="eyebrow eyebrow--secondary">{{ $data['eyebrow'] }}</p>
        @endif

        @if (!empty($data['title']))
          <h2 class="section-title text-text-on-dark" id="newsletter-title">
            {!! $data['title'] !!}
          </h2>
        @endif

        @if (!empty($data['text']))
          <p class="mb-0 text-[length:var(--text-section-body)] leading-relaxed text-sand">
            {{ $data['text'] }}
          </p>
        @endif
      </div>

      <div class="relative animate-fade-left">
        <form
          id="newsletterForm"
          class="newsletter__form"
          aria-label="Newsletter-Anmeldung"
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
