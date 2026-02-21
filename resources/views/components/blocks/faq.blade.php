@props(['block'])

@php
    $data = $block->data;
    $faqItems = $data['items'] ?? [];
@endphp

@if(!empty($faqItems) && is_array($faqItems))
    <x-seo.faq-schema :items="$faqItems" />
@endif

<section class="section section--large faq-section" id="faq">
    <div class="container">
        <div class="faq__layout">
            <div class="faq__header fade-in">
                @if(!empty($data['eyebrow']))
                    <p class="eyebrow">{{ $data['eyebrow'] }}</p>
                @endif

                @if(!empty($data['title']))
                    <h2 class="section-title faq__title">{!! $data['title'] !!}</h2>
                @endif

                @if(!empty($data['intro']))
                    <p class="faq__intro">{{ $data['intro'] }}</p>
                @endif
            </div>

            @if(!empty($faqItems) && is_array($faqItems))
                <div class="faq__list fade-in fade-in-delay-1">
                    @foreach($faqItems as $item)
                        @if(!empty($item['question']) && !empty($item['answer']))
                            <details class="faq-item" name="faq-accordion" data-m:toggle="action=faq_toggle;element=details;target=question;location=faq_section">
                                <summary class="faq-item__question" data-umami-event="faq-expand" data-umami-event-question="{{ Str::limit($item['question'], 50) }}">
                                    <span>{{ $item['question'] }}</span>
                                    <span class="faq-item__icon" aria-hidden="true"></span>
                                </summary>
                                <div class="faq-item__answer">
                                    <div class="faq-item__answer-inner">
                                        {!! $item['answer'] !!}
                                    </div>
                                </div>
                            </details>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>
