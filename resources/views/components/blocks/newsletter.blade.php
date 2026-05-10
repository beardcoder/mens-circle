@props (['block'])

@php
    $data = $block->data;
@endphp

<section
  class="section-y-lg relative isolate overflow-hidden bg-gradient-to-b from-[var(--color-earth-dark)] to-[var(--color-earth-deep)] text-[var(--color-parchment)]"
  id="newsletter"
  aria-labelledby="newsletter-title"
>
  <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
    <div
      class="absolute inset-0 [background:radial-gradient(ellipse_70%_60%_at_30%_50%,color-mix(in_oklch,var(--accent)_18%,transparent)_0%,transparent_55%)]"
    ></div>
    <span
      class="absolute -top-[15vw] -right-[15vw] block h-[50vw] w-[50vw] rounded-full border border-[var(--color-sand)]/15 animate-breathe [animation-duration:22s]"
    ></span>
  </div>

  <div class="container-page grid gap-12 md:grid-cols-2 md:items-center">
    <div
      class="animate-reveal-up timeline-view animate-range-[entry_5%_cover_25%]"
    >
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
        <p class="mt-6 text-lg leading-[1.85] text-[var(--color-sand)]">{{ $data['text'] }}</p>
      @endif
    </div>

    <form
      x-data="newsletterForm"
      @submit.prevent="submit($event)"
      class="flex flex-col gap-3 sm:flex-row sm:items-center animate-reveal-up timeline-view animate-range-[entry_5%_cover_25%]"
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
        class="flex-1 rounded-full border border-white/15 bg-white/5 px-6 py-4 text-base text-[var(--color-parchment)] placeholder:text-[var(--color-sand)]/60 backdrop-blur-sm transition-colors focus:border-[var(--color-terracotta-light)] focus:outline-none focus:bg-white/10"
      />
      <button type="submit" class="btn btn-primary">Anmelden</button>
    </form>
  </div>
</section>
