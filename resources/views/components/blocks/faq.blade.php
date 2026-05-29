@props (['block'])

@php
    use App\Seo\Data\FaqItem;
    use App\Seo\Schemas\FaqPageSchema;

    $data = $block->data;
    $anchor = $data['anchor'] ?? 'faq';
    $faqItems = is_array($data['items'] ?? null) ? $data['items'] : [];

    $schemaItems = collect($faqItems)
        ->filter(static fn(array $item): bool => !empty($item['question']) && !empty($item['answer']))
        ->map(static fn(array $item): FaqItem => new FaqItem($item['question'], $item['answer']))
        ->values()
        ->all();
@endphp

@if ($schemaItems !== [])
  @push ('structured_data')
    {!! (new FaqPageSchema($schemaItems))->toScript() !!}
  @endpush
@endif

<section class="section section--large faq-section" id="{{ $anchor }}">
  <div class="container">
    <div
      class="section-header section-header--start faq__header"
      data-reveal-group
    >
      @if (!empty($data['eyebrow']))
        <p class="eyebrow" data-reveal="up">{{ $data['eyebrow'] }}</p>
      @endif

      @if (!empty($data['title']))
        <h2 class="section-title" data-reveal="blur">{!! $data['title'] !!}</h2>
      @endif

      @if (!empty($data['intro']))
        <p class="section-intro" data-reveal="up">{{ $data['intro'] }}</p>
      @endif
    </div>

    @if ($faqItems !== [])
      <div class="faq__list" data-reveal-group="80">
        @foreach ($faqItems as $index => $item)
          @if (!empty($item['question']) && !empty($item['answer']))
            <details
              class="accordion-item"
              name="{{ $anchor }}"
              data-reveal="up"
            >
              <summary
                class="accordion-item__trigger"
                data-umami-event="faq-expand"
                data-umami-event-question="{{ Str::limit($item['question'], 50) }}"
              >
                <span>{{ $item['question'] }}</span>
                <span class="accordion-item__icon" aria-hidden="true"></span>
              </summary>
              <div class="accordion-item__body">
                <div class="accordion-item__content">
                  {!! $item['answer'] !!}
                </div>
              </div>
            </details>
          @endif
        @endforeach
      </div>
    @endif
  </div>
</section>
