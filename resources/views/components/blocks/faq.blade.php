<section class="section section--large faq-section" id="faq">
    <div class="container">
        <div class="faq__layout">
            <div class="faq__header fade-in">
                @if(!empty($block['eyebrow']))
                    <p class="faq__eyebrow">{{ $block['eyebrow'] }}</p>
                @endif

                @if(!empty($block['title']))
                    <h2 class="faq__title">{!! $block['title'] !!}</h2>
                @endif

                @if(!empty($block['intro']))
                    <p class="faq__intro">{{ $block['intro'] }}</p>
                @endif
            </div>

            @if(!empty($block['items']) && is_array($block['items']))
                <div class="faq__list fade-in fade-in-delay-1">
                    @foreach($block['items'] as $item)
                        <div class="faq-item">
                            @if(!empty($item['question']))
                                <button class="faq-item__question" aria-expanded="false">
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
