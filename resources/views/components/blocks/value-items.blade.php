@props(['block'])

@php
    $data = $block->data;
@endphp

<section class="section values-section">
    <div class="container">
        <div class="section__header fade-in">
            @if(!empty($data['eyebrow']))
                <p class="eyebrow">{{ $data['eyebrow'] }}</p>
            @endif

            @if(!empty($data['title']))
                <h2 class="section-title">{{ $data['title'] }}</h2>
            @endif
        </div>

        @if(!empty($data['items']) && is_array($data['items']))
            <div class="intro__values stagger-children">
                @foreach($data['items'] as $item)
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
