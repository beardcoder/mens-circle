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

<section class="section-y-lg" id="faq">
  <div class="container-narrow">
    <div x-reveal class="mb-12">
      @if (!empty($data['eyebrow']))
        <p class="eyebrow">{{ $data['eyebrow'] }}</p>
      @endif
      @if (!empty($data['title']))
        <h2 class="section-title-lg">{!! $data['title'] !!}</h2>
      @endif
      @if (!empty($data['intro']))
        <p class="mt-4 text-lg text-[var(--fg-muted)]">{{ $data['intro'] }}</p>
      @endif
    </div>

    @if (!empty($faqItems))
      <div class="flex flex-col gap-3" x-data="{ openIndex: null }">
        @foreach ($faqItems as $i => $item)
          @if (!empty($item['question']) && !empty($item['answer']))
            <div
              class="overflow-hidden rounded-xl border border-[var(--border)] bg-[var(--bg-alt)]"
            >
              <button
                type="button"
                @click="openIndex = openIndex === {{ $i }} ? null : {{ $i }}"
                :aria-expanded="openIndex === {{ $i }}"
                class="flex w-full items-center justify-between gap-4 px-6 py-5 text-left font-display text-lg font-medium text-[var(--fg)] transition-colors hover:text-[var(--accent)]"
                data-umami-event="faq-expand"
                data-umami-event-question="{{ Str::limit($item['question'], 50) }}"
              >
                <span>{{ $item['question'] }}</span>
                <svg
                  :class="openIndex === {{ $i }} ? 'rotate-180' : ''"
                  class="h-5 w-5 shrink-0 transition-transform duration-300"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  aria-hidden="true"
                ><polyline points="6 9 12 15 18 9" /></svg>
              </button>
              <div x-show="openIndex === {{ $i }}" x-collapse>
                <div class="prose-block px-6 pb-6 text-[var(--fg-muted)]">
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
