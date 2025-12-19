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
            <div class="values stagger-children">
                @foreach($block['items'] as $item)
                    <div class="value-item">
                        @if(!empty($item['number']))
                            <span class="value-item__number">{{ $item['number'] }}</span>
                        @endif

                        @if(!empty($item['title']))
                            <h3>{{ $item['title'] }}</h3>
                        @endif

                        @if(!empty($item['description']))
                            <p>{{ $item['description'] }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
