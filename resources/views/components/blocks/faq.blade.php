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
                        <div class="faq-item">
                            @if(!empty($item['question']))
                                <button class="faq-item__question" aria-expanded="false" aria-controls="faq-answer-{{ $loop->index }}" data-m:click="action=faq_click;element=button;target=question;location=faq_section">
                                    <span>{{ $item['question'] }}</span>
                                    <span class="faq-item__icon" aria-hidden="true"></span>
                                </button>
                            @endif

                            @if(!empty($item['answer']))
                                <div class="faq-item__answer" id="faq-answer-{{ $loop->index }}" role="region">
                                    <div class="faq-item__answer-inner">
                                        {!! $item['answer'] !!}
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>
