<section class="section section--large faq-section" id="faq">
    <div class="container">
        <div class="faq__layout">
            <div class="faq__header fade-in">
                @if(!empty($block['data']['eyebrow']))
                    <p class="eyebrow">{{ $block['data']['eyebrow'] }}</p>
                @endif

                @if(!empty($block['data']['title']))
                    <h2 class="section-title faq__title">{!! $block['data']['title'] !!}</h2>
                @endif

                @if(!empty($block['data']['intro']))
                    <p class="faq__intro">{{ $block['data']['intro'] }}</p>
                @endif
            </div>

            @if(!empty($block['data']['items']) && is_array($block['data']['items']))
                <div class="faq__list fade-in fade-in-delay-1">
                    @foreach($block['data']['items'] as $item)
                        <div class="faq-item">
                            @if(!empty($item['question']))
                                <button class="faq-item__question" aria-expanded="false" data-m:click="action=faq_click;element=button;target=question;location=faq_section">
                                    <span>{{ $item['question'] }}</span>
                                    <span class="faq-item__icon"></span>
                                </button>
                            @endif

                            @if(!empty($item['answer']))
                                <div class="faq-item__answer">
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
