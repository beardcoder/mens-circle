@if(config('analytics.umami.enabled') && config('analytics.umami.website_id'))
    @php
        $scriptUrl = (string) config('analytics.umami.script_url', 'https://va.letsbenow.de/script.js');
        $hostUrl = preg_replace('#/script\\.js$#', '', $scriptUrl) ?: 'https://va.letsbenow.de';
    @endphp
    <script
        defer
        src="{{ $scriptUrl }}"
        data-website-id="{{ config('analytics.umami.website_id') }}"
        data-host-url="{{ $hostUrl }}"
    ></script>
@endif
