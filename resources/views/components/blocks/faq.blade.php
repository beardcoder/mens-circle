@props (['block'])

@php
    use App\Seo\Data\FaqItem;
    use App\Seo\Schemas\FaqPageSchema;

    $data = $block->data;
    $faqItems = $data['items'] ?? [];

    $schemaItems = collect($faqItems)
        ->filter(static fn($item): bool => !empty($item['question']) && !empty($item['answer']))
        ->map(static fn($item): FaqItem => new FaqItem($item['question'], $item['answer']))
        ->values()
        ->all();
@endphp

@if (count($schemaItems) > 0)
  @push ('structured_data')
    {!! (new FaqPageSchema($schemaItems))->toScript() !!}
  @endpush
@endif

<section class="section-y-lg editorial-light" id="faq">
  <div class="container-narrow">
    <div
      class="section-header animate-reveal-up timeline-view animate-range-[entry_5%_cover_25%]"
    >
      @if (!empty($data['eyebrow']))
        <p class="eyebrow">{{ $data['eyebrow'] }}</p>
      @endif
      @if (!empty($data['title']))
        <h2 class="section-title-lg split-title">{!! $data['title'] !!}</h2>
      @endif
      @if (!empty($data['intro']))
        <p class="section-intro">{{ $data['intro'] }}</p>
      @endif
    </div>

    @if (!empty($faqItems))
      <div class="flex flex-col" x-data="{ openIndex: null }">
        @foreach ($faqItems as $i => $item)
          @if (!empty($item['question']) && !empty($item['answer']))
            <div
              class="border-b border-[color-mix(in_oklch,var(--border)_78%,transparent)] [&:first-child]:border-t"
            >
              <button
                type="button"
                @click="openIndex = openIndex === {{ $i }} ? null : {{ $i }}"
                :aria-expanded="openIndex === {{ $i }}"
                id="faq-question-{{ $i }}"
                aria-controls="faq-answer-{{ $i }}"
                class="group flex w-full items-center justify-between gap-6 py-7 text-left font-display text-[clamp(1.3rem,1.05rem+0.8vw,1.9rem)] font-medium leading-[1.35] text-[var(--fg)] transition-colors hover:text-[color-mix(in_oklch,var(--accent)_65%,var(--fg))]"
                data-umami-event="faq-expand"
                data-umami-event-question="{{ Str::limit($item['question'], 50) }}"
              >
                <span>{{ $item['question'] }}</span>
                <span
                  :class="openIndex === {{ $i }} ? 'rotate-45 border-[var(--accent)] text-[var(--accent)]' : ''"
                  class="grid h-10 w-10 shrink-0 place-items-center rounded-full border border-[var(--border)] text-[var(--fg-muted)] transition-all duration-300 group-hover:border-[var(--accent)] group-hover:text-[var(--accent)]"
                  aria-hidden="true"
                >
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-4 w-4">
                    <line x1="12" y1="5" x2="12" y2="19" />
                    <line x1="5" y1="12" x2="19" y2="12" />
                  </svg>
                </span>
              </button>
              <div
                id="faq-answer-{{ $i }}"
                role="region"
                aria-labelledby="faq-question-{{ $i }}"
                x-show="openIndex === {{ $i }}"
                x-collapse
              >
                <div
                  class="prose-block pb-7 pr-2 md:pr-16 text-[var(--fg-muted)] leading-[1.9]"
                >
                  {!! $item['answer'] !!}
                </div>
              </div>
            </div>
          @endif
        @endforeach
      </div>
    @endif
  </div>
</section>
