@props (['block'])

@php
    $data = $block->data;
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

<section class="section section--large faq-section" id="faq">
  <div class="container">
    <div
      class="section-header section-header--start faq__header"
      data-anim-group
    >
      <p class="story-kicker" data-anim="trace">
        <span>Kapitel 06</span>
        <span>Klärung</span>
      </p>

      @if (!empty($data['eyebrow']))
        <p class="eyebrow" data-anim="rise">{{ $data['eyebrow'] }}</p>
      @endif

      @if (!empty($data['title']))
        <h2 class="section-title" data-anim="rise">{!! $data['title'] !!}</h2>
      @endif

      @if (!empty($data['intro']))
        <p class="section-intro" data-anim="rise">{{ $data['intro'] }}</p>
      @endif
    </div>

    @if (!empty($faqItems) && is_array($faqItems))
      <div class="faq__list" data-anim-group>
        @foreach ($faqItems as $item)
          @if (!empty($item['question']) && !empty($item['answer']))
            <details
              data-anim="rise"
              class="accordion-item"
              name="faq-accordion"
              data-m:toggle="action=faq_toggle;element=details;target=question;location=faq_section"
            >
              <summary
                class="accordion-item__trigger"
                data-umami-event="faq-expand"
                data-umami-event-question="{{ Str::limit($item['question'], 50) }}"
              >
                <span>{{ $item['question'] }}</span>
                <span class="accordion-item__icon" aria-hidden="true"></span>
              </summary>
              <div class="accordion-item__content">
                <div class="accordion-item__body">{!! $item['answer'] !!}</div>
              </div>
            </details>
          @endif
        @endforeach
      </div>
    @endif
  </div>
</section>
