@props(['subscriptionId' => null, 'eventName' => 'newsletter'])

@if(config('analytics.umami.enabled') && config('analytics.umami.tracking_pixel_url'))
    @php
        $pixelUrl = config('analytics.umami.tracking_pixel_url');

        // Add query parameters for tracking
        $params = [
            'event' => $eventName,
        ];

        if ($subscriptionId) {
            $params['sid'] = $subscriptionId;
        }

        $pixelUrl .= '?' . http_build_query($params);
    @endphp
    <img src="{{ $pixelUrl }}" width="1" height="1" alt="" style="display:block;width:1px;height:1px;border:0;opacity:0;" />
@endif
