<?php

declare(strict_types=1);

$appUrl = (string) env('APP_URL', 'localhost');
$defaultHost = parse_url($appUrl, PHP_URL_HOST);

return [
    /*
     * The hostname(s) this Laravel app is listening to.
     */
    'host' => explode(',', (string) env('VARNISH_HOST', \is_string($defaultHost) ? $defaultHost : 'localhost')),

    /*
     * The location of the file containing the administrative password.
     */
    'administrative_secret' => env('VARNISH_ADMIN_SECRET', '/etc/varnish/secret'),

    /*
     * The port where the administrative tasks may be sent to.
     */
    'administrative_port' => intval(env('VARNISH_ADMIN_PORT', 6082)),

    /*
     * The default amount of minutes that content rendered using the `CacheWithVarnish`
     * middleware should be cached.
     */
    'cache_time_in_minutes' => intval(env('VARNISH_CACHE_TIME', 60 * 24)),

    /*
     * The name of the header that triggers Varnish to cache the response.
     */
    'cacheable_header_name' => 'X-Cacheable',
];
