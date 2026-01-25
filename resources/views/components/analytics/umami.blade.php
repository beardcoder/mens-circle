@if(config('analytics.umami.enabled') && config('analytics.umami.website_id'))
    <!-- Umami Analytics -->
    <script
        defer
        src="{{ config('analytics.umami.script_url') }}"
        data-website-id="{{ config('analytics.umami.website_id') }}"
    ></script>
@endif
