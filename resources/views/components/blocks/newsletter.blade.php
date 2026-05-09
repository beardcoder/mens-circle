@props (['block'])

@php
    $data = $block->data;
@endphp

<section
  class="section-y bg-[var(--bg-deep)] text-[var(--color-parchment)]"
  id="newsletter"
  aria-labelledby="newsletter-title"
>
  <div class="container-page grid gap-12 md:grid-cols-2 md:items-center">
    <div x-reveal>
      @if (!empty($data['eyebrow']))
        <p class="eyebrow text-[var(--color-terracotta-light)]">{{ $data['eyebrow'] }}</p>
      @endif
      @if (!empty($data['title']))
        <h2
          class="section-title-lg text-[var(--color-parchment)]"
          id="newsletter-title"
        >
          {!! $data['title'] !!}
        </h2>
      @endif
      @if (!empty($data['text']))
        <p class="mt-4 text-lg text-[var(--color-sand)]">{{ $data['text'] }}</p>
      @endif
    </div>

    <form
      x-data="newsletterForm"
      @submit.prevent="submit($event)"
      x-reveal
      class="flex flex-col gap-3 sm:flex-row sm:items-center"
      aria-label="Newsletter-Anmeldung"
    >
      <label for="newsletter-email" class="sr-only">E-Mail-Adresse</label>
      <input
        type="email"
        id="newsletter-email"
        name="email"
        x-model="email"
        placeholder="Deine E-Mail-Adresse"
        required
        autocomplete="email"
        inputmode="email"
        class="flex-1 rounded-full border border-white/20 bg-white/10 px-5 py-3 text-[var(--color-parchment)] placeholder:text-[var(--color-sand)]/60 backdrop-blur-sm focus:border-[var(--color-terracotta-light)] focus:outline-none"
      />
      <button type="submit" class="btn btn-primary">Anmelden</button>
    </form>
  </div>
</section>
