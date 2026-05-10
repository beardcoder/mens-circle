@extends ('layouts.app')

@section ('title', 'Atemübung – Männerkreis Niederbayern/ Straubing')
@section ('meta_description', 'Eine geführte, interaktive Atemübung im Stil der Wim-Hof-Methode.')
@section ('og_title', 'Atemübung – Männerkreis Niederbayern/ Straubing')

@php
    use App\Seo\Data\BreadcrumbItem;
    use App\Seo\Schemas\BreadcrumbSchema;
    use App\Seo\Schemas\WebPageSchema;
@endphp

@push ('structured_data')
  {!! (new BreadcrumbSchema([
        new BreadcrumbItem('Startseite', route('home')),
        new BreadcrumbItem('Atemübung', route('breathing.show')),
    ]))->toScript() !!}
  {!! (new WebPageSchema(
        title: 'Atemübung',
        description: 'Eine geführte, interaktive Atemübung im Stil der Wim-Hof-Methode.',
    ))->toScript() !!}
@endpush

@section ('content')
  <section
    data-hero
    class="relative isolate flex min-h-[80svh] items-end overflow-hidden bg-gradient-to-b from-[var(--color-earth-deep)] to-[var(--color-earth-dark)] pb-20 text-[var(--color-parchment)]"
    style="min-block-size: min(720px, 80svh)"
  >
    <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
      <div
        class="absolute inset-0 [background:radial-gradient(ellipse_60%_50%_at_30%_40%,color-mix(in_oklch,var(--color-sage)_25%,transparent)_0%,transparent_50%),radial-gradient(ellipse_60%_50%_at_70%_70%,color-mix(in_oklch,var(--accent)_15%,transparent)_0%,transparent_45%)]"
      ></div>
    </div>
    <div class="hero-decor" aria-hidden="true">
      <span
        class="-top-[10vw] -left-[20vw] h-[60vw] w-[60vw] animate-breathe [animation-duration:20s]"
      ></span>
      <span
        class="-bottom-[40vw] -right-[40vw] h-[80vw] w-[80vw] animate-breathe [animation-delay:-5s] [animation-duration:28s]"
      ></span>
    </div>

    <div class="container-page relative z-10 w-full">
      <p class="eyebrow text-[var(--color-terracotta-light)] animate-reveal-up timeline-view animate-range-[entry_5%_cover_25%]">Bewusster Atem</p>
      <h1
        class="hero-title animate-reveal-up timeline-view animate-range-[entry_5%_cover_25%]"
      >
        Atem<span class="text-italic">übung</span>
      </h1>
      <p class="mt-8 max-w-[520px] text-base leading-[1.9] text-[var(--color-sand)] md:text-lg animate-reveal-up timeline-view animate-range-[entry_5%_cover_25%]">Drei Runden bewusster Atem im Stil der Wim-Hof-Methode. Für Klarheit, Energie und innere Ruhe.</p>
    </div>
  </section>
  <section class="section-y">
    <div
      class="container-page grid gap-12 lg:grid-cols-[1fr_2fr] lg:items-start"
    >
      <aside
        class="rounded-2xl bg-[var(--bg-alt)] p-8 animate-reveal-up timeline-view animate-range-[entry_5%_cover_25%]"
      >
        <h2 class="font-display text-2xl font-semibold">So funktioniert es</h2>
        <ol class="mt-4 flex flex-col gap-3 text-sm text-[var(--fg-muted)]">
          <li>
            <strong class="text-[var(--fg)]">Tief atmen:</strong> 30 kräftige
            Atemzüge — vollständig einatmen, locker ausatmen.
          </li>
          <li>
            <strong class="text-[var(--fg)]">Halten:</strong> Nach der letzten
            Ausatmung den Atem so lange wie möglich anhalten.
          </li>
          <li>
            <strong class="text-[var(--fg)]">Erholung:</strong> Tief einatmen
            und 15 Sekunden halten. Dann normal weiteratmen.
          </li>
        </ol>
        <p class="mt-6 rounded-lg border border-[var(--color-warning)]/40 bg-[var(--color-warning)]/10 px-4 py-3 text-sm">
          <strong>Wichtig:</strong> Übe niemals im Wasser oder beim Autofahren.
        </p>
      </aside>

      <div
        x-data="breathing"
        :data-phase="phase"
        class="flex flex-col items-center gap-10"
      >
        {{-- Visual stage --}}
        <div
          class="relative grid h-80 w-80 place-items-center"
          aria-live="polite"
        >
          <span
            :class="phase === 'breathing'
              ? 'animate-breathe'
              : phase === 'retention'
                ? 'scale-110 opacity-80'
                : phase === 'recovery'
                  ? 'scale-90 opacity-50'
                  : ''"
            class="absolute inset-0 rounded-full border-2 border-[var(--accent)]/30 transition-all duration-1000 ease-[var(--ease-ambient)]"
          ></span>
          <span
            :class="phase === 'breathing'
              ? 'animate-breathe [animation-delay:0.5s]'
              : ''"
            class="absolute inset-8 rounded-full border-2 border-[var(--accent)]/50 transition-all duration-1000"
          ></span>
          <span
            :class="phase === 'breathing'
              ? 'animate-breathe [animation-delay:1s]'
              : ''"
            class="absolute inset-16 rounded-full bg-gradient-to-br from-[var(--accent)]/40 to-[var(--accent)]/10 transition-all duration-1000"
          ></span>
          <div class="relative text-center">
            <div
              class="font-display text-3xl font-semibold"
              x-text="phaseLabel"
            ></div>
            <div
              class="mt-2 text-sm text-[var(--fg-muted)]"
              x-text="counter"
            ></div>
          </div>
        </div>

        {{-- Meta --}}
        <dl class="grid w-full max-w-md grid-cols-3 gap-4 text-center text-sm">
          <div class="rounded-xl bg-[var(--bg-alt)] p-3">
            <dt
              class="text-xs uppercase tracking-widest text-[var(--fg-muted)]"
            >
              Runde
            </dt>
            <dd class="mt-1 font-display text-lg font-semibold">
              <span x-text="round"></span> / <span x-text="rounds"></span>
            </dd>
          </div>
          <div class="rounded-xl bg-[var(--bg-alt)] p-3">
            <dt
              class="text-xs uppercase tracking-widest text-[var(--fg-muted)]"
            >
              Atemzug
            </dt>
            <dd class="mt-1 font-display text-lg font-semibold">
              <span x-text="breath"></span> / <span x-text="breaths"></span>
            </dd>
          </div>
          <div class="rounded-xl bg-[var(--bg-alt)] p-3">
            <dt
              class="text-xs uppercase tracking-widest text-[var(--fg-muted)]"
            >
              Zeit
            </dt>
            <dd
              class="mt-1 font-display text-lg font-semibold tabular-nums"
              x-text="timer"
            ></dd>
          </div>
        </dl>

        {{-- Controls --}}
        <div class="flex flex-wrap items-center justify-center gap-3">
          <button
            type="button"
            x-show="isIdle"
            @click="start"
            class="btn btn-primary btn-large"
            data-umami-event="breathing-start"
          >
            <svg viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5" aria-hidden="true"><path d="M8 5.14v13.72a1 1 0 0 0 1.54.84l10.3-6.86a1 1 0 0 0 0-1.68L9.54 4.3A1 1 0 0 0 8 5.14Z" /></svg>
            <span
              x-text="
                phase === 'complete' ? 'Erneut starten' : 'Atemübung starten'
              "
            ></span>
          </button>
          <button
            type="button"
            x-show="isHolding"
            @click="releaseHold"
            class="btn btn-secondary"
            data-umami-event="breathing-resume"
          >
            <span
              x-text="phase === 'recovery' ? 'Weiteratmen' : 'Atem freigeben'"
            ></span>
          </button>
          <button
            type="button"
            @click="reset"
            class="btn btn-ghost"
            data-umami-event="breathing-reset"
          >
            Zurücksetzen
          </button>
        </div>

        {{-- Settings --}}
        <div class="grid w-full max-w-md gap-6">
          <div>
            <span class="text-sm font-medium"
              >Atemzüge je Runde:
              <span
                x-text="breaths"
                class="font-display text-lg text-[var(--accent)]"
              ></span
            ></span>
            <input
              type="range"
              min="10"
              max="60"
              step="5"
              :value="breaths"
              @input="setBreaths(parseInt($event.target.value))"
              :disabled="!isIdle"
              class="mt-2 w-full accent-[var(--accent)]"
            />
          </div>
          <div class="flex items-center justify-between">
            <span class="text-sm font-medium">Runden</span>
            <div class="flex items-center gap-3">
              <button
                type="button"
                @click="stepRounds(-1)"
                :disabled="!isIdle"
                class="grid h-9 w-9 place-items-center rounded-full border border-[var(--border)] disabled:opacity-50"
              >
                −
              </button>
              <span
                class="font-display text-lg font-semibold w-6 text-center"
                x-text="rounds"
              ></span>
              <button
                type="button"
                @click="stepRounds(1)"
                :disabled="!isIdle"
                class="grid h-9 w-9 place-items-center rounded-full border border-[var(--border)] disabled:opacity-50"
              >
                +
              </button>
            </div>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-sm font-medium">Erholungs-Halt (Sek.)</span>
            <div class="flex items-center gap-3">
              <button
                type="button"
                @click="stepRecovery(-1)"
                :disabled="!isIdle"
                class="grid h-9 w-9 place-items-center rounded-full border border-[var(--border)] disabled:opacity-50"
              >
                −
              </button>
              <span
                class="font-display text-lg font-semibold w-8 text-center"
                x-text="recoveryHold"
              ></span>
              <button
                type="button"
                @click="stepRecovery(1)"
                :disabled="!isIdle"
                class="grid h-9 w-9 place-items-center rounded-full border border-[var(--border)] disabled:opacity-50"
              >
                +
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
