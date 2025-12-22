<section class="intro-section" id="ueber">
    <div class="intro__layout">
        <div class="intro__left">
            @if(!empty($block->data['eyebrow']))
                <p class="intro__eyebrow fade-in">{{ $block->data['eyebrow'] }}</p>
            @endif

            @if(!empty($block->data['title']))
                <h2 class="intro__title fade-in fade-in-delay-1">
                    {!! $block->data['title'] !!}
                </h2>
            @endif

            @if(!empty($block->data['text']))
                <p class="intro__text fade-in fade-in-delay-2">
                    {{ $block->data['text'] }}
                </p>
            @endif

            @if(!empty($block->data['values']) && is_array($block->data['values']))
                <div class="intro__values stagger-children">
                    @foreach($block->data['values'] as $value)
                        <div class="value-item">
                            @if(!empty($value['number']))
                                <span class="value-item__number">{{ $value['number'] }}</span>
                            @endif
                            <div class="value-item__content">
                                @if(!empty($value['title']))
                                    <h4>{{ $value['title'] }}</h4>
                                @endif
                                @if(!empty($value['description']))
                                    <p>{{ $value['description'] }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="intro__right">
            <div class="intro__image-area">
                <div class="intro__image-circles"></div>
                @if(!empty($block->data['quote']))
                    <p class="intro__image-text">
                        {!! $block->data['quote'] !!}
                    </p>
                @endif
            </div>
        </div>
    </div>
</section>
