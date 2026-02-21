@if(config('analytics.umami.enabled') && config('analytics.umami.website_id'))
    <!-- Umami Analytics -->
    <script
        defer
        src="/va/script.js"
        data-website-id="{{ config('analytics.umami.website_id') }}"
        data-host-url="/va"
    ></script>
@endif
