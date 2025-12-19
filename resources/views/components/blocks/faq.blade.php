<section class="section faq-section" id="faq">
    <div class="container">
        <div class="section__header fade-in">
            @if(!empty($block['eyebrow']))
                <p class="section__eyebrow">{{ $block['eyebrow'] }}</p>
            @endif

            @if(!empty($block['title']))
                <h2>{{ $block['title'] }}</h2>
            @endif
        </div>

        @if(!empty($block['items']) && is_array($block['items']))
            <div class="faq fade-in fade-in-delay-1">
                @foreach($block['items'] as $index => $item)
                    <div class="faq-item">
                        @if(!empty($item['question']))
                            <button class="faq-item__question" aria-expanded="false">
                                {{ $item['question'] }}
                                <svg class="faq-item__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </button>
                        @endif

                        @if(!empty($item['answer']))
                            <div class="faq-item__answer">
                                <p>{{ $item['answer'] }}</p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
