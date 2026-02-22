@if(config('analytics.umami.enabled') && config('analytics.umami.website_id'))
    <script
        defer
        src="/va/script.js"
        data-website-id="{{ config('analytics.umami.website_id') }}"
        data-host-url="https://va.letsbenow.de"
    ></script>
@endif
