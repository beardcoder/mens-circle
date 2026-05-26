@props (['block'])

@php
    $data = $block->data;
    $anchor = $data['anchor'] ?? 'faq';
    $faqItems = $data['items'] ?? [];
@endphp

@php
    use App\Seo\Data\FaqItem;
    use App\Seo\Schemas\FaqPageSchema;

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

<section class="section section--large faq-section" id="{{ $anchor }}">
  <div class="container">
    <div class="section-header section-header--start faq__header">
      @if (!empty($data['eyebrow']))
        <p class="eyebrow">{{ $data['eyebrow'] }}</p>
      @endif

      @if (!empty($data['title']))
        <h2 class="section-title">{!! $data['title'] !!}</h2>
      @endif

      @if (!empty($data['intro']))
        <p class="section-intro">{{ $data['intro'] }}</p>
      @endif
    </div>

    @if (!empty($faqItems) && is_array($faqItems))
      <div class="faq__list">
        @foreach ($faqItems as $index => $item)
          @if (!empty($item['question']) && !empty($item['answer']))
            <div
              class="accordion-item"
              data-lume="accordion"
            >
              <button
                class="accordion-item__trigger"
                data-umami-event="faq-expand"
                data-lume-part="control"
                data-umami-event-question="{{ Str::limit($item['question'], 50) }}"
                aria-expanded="true"
                aria-controls="faq-item-{{ $index }}"
              >
                <span>{{ $item['question'] }}</span>
                <span class="accordion-item__icon" aria-hidden="true"></span>
              </button>
              <div class="accordion-item__body" id="faq-item-{{ $index }}" data-lume-part="body" data-open="false">
                  <div class="accordion-item__content">{!! $item['answer'] !!}</div>
              </div>
            </div>
          @endif
        @endforeach
      </div>
    @endif
  </div>
</section>
