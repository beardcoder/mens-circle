<section class="section values-section">
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
            <div class="intro__values stagger-children">
                @foreach($block['items'] as $item)
                    <div class="value-item">
                        @if(!empty($item['number']))
                            <span class="value-item__number">{{ $item['number'] }}</span>
                        @endif

                        <div class="value-item__content">
                            @if(!empty($item['title']))
                                <h4>{{ $item['title'] }}</h4>
                            @endif

                            @if(!empty($item['description']))
                                <p>{{ $item['description'] }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
