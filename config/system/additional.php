<?php

declare(strict_types=1);

$GLOBALS['TYPO3_CONF_VARS']['SYS']['trustedHostsPattern'] = env('TRUSTED_HOSTS_PATTERN', '.*');
$GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'] = env('SITENAME', 'Männerkreis Niederbayern/ Straubing');
$GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask'] = env('DEV_IPMASK', '');
$GLOBALS['TYPO3_CONF_VARS']['SYS']['displayErrors'] = env('DISPLAY_ERRORS', 0);
$GLOBALS['TYPO3_CONF_VARS']['BE']['debug'] = env('DEBUG', false);
$GLOBALS['TYPO3_CONF_VARS']['SYS']['reverseProxySSL'] = env('REVERSE_PROXY_SSL', '*');
$GLOBALS['TYPO3_CONF_VARS']['SYS']['reverseProxyIP'] = env('REVERSE_PROXY_IP', '*');
$GLOBALS['TYPO3_CONF_VARS']['SYS']['reverseProxyHeaderMultiValue'] = env('REVERSE_PROXY_HEADER_MULTI_VALUE', 'first');

if (env('TYPO3_DB_HOST')) {
    $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default'] = [
        'driver' => env('TYPO3_DB_DRIVER') ?: 'mysqli',
        'host' => env('TYPO3_DB_HOST', 'db'),
        'port' => (int)(env('TYPO3_DB_PORT', 3306)),
        'dbname' => env('TYPO3_DB_NAME', 'db'),
        'user' => env('TYPO3_DB_USER', 'db'),
        'password' => env('TYPO3_DB_PASSWORD', 'db'),
        'charset' => 'utf8mb4',
        'tableoptions' => [
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci',
        ],
    ];
}

// Sentry error tracking
if (env('SENTRY_DSN')) {
    \Sentry\init([
        'dsn' => (string) env('SENTRY_DSN'),
        'environment' => (string) env('SENTRY_ENVIRONMENT', 'production'),
        'release' => (string) env('SENTRY_RELEASE', ''),
        'traces_sample_rate' => (float) env('SENTRY_TRACES_SAMPLE_RATE', 0.0),
        'send_default_pii' => false,
    ]);
}

if (env('TYPO3_MAIL_TRANSPORT')) {
    $GLOBALS['TYPO3_CONF_VARS']['MAIL']['transport'] = env('TYPO3_MAIL_TRANSPORT');

    if (env('TYPO3_MAIL_SMTP_SERVER')) {
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['transport_smtp_server'] = env('TYPO3_MAIL_SMTP_SERVER');
    }
    if (env('TYPO3_MAIL_SMTP_ENCRYPT')) {
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['transport_smtp_encrypt'] = env('TYPO3_MAIL_SMTP_ENCRYPT');
    }
    if (env('TYPO3_MAIL_SMTP_USERNAME')) {
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['transport_smtp_username'] = env('TYPO3_MAIL_SMTP_USERNAME');
    }
    if (env('TYPO3_MAIL_SMTP_PASSWORD')) {
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['transport_smtp_password'] = env('TYPO3_MAIL_SMTP_PASSWORD');
    }
}