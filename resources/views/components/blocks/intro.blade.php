<section class="intro-section" id="ueber">
    <div class="intro__layout">
        <div class="intro__left">
            @if(!empty($block['eyebrow']))
                <p class="intro__eyebrow fade-in">{{ $block['eyebrow'] }}</p>
            @endif

            @if(!empty($block['title']))
                <h2 class="intro__title fade-in fade-in-delay-1">
                    {!! $block['title'] !!}
                </h2>
            @endif

            @if(!empty($block['text']))
                <p class="intro__text fade-in fade-in-delay-2">
                    {{ $block['text'] }}
                </p>
            @endif

            @if(!empty($block['values']) && is_array($block['values']))
                <div class="intro__values stagger-children">
                    @foreach($block['values'] as $value)
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
                @if(!empty($block['quote']))
                    <p class="intro__image-text">
                        {!! $block['quote'] !!}
                    </p>
                @endif
            </div>
        </div>
    </div>
</section>
