<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Pushover API Token
    |--------------------------------------------------------------------------
    |
    | Your Pushover application API token.
    | Create an application at https://pushover.net/apps/build
    |
    */
    'token' => env('PUSHOVER_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Pushover User Key
    |--------------------------------------------------------------------------
    |
    | The user/group key of the notification recipient.
    | Found at https://pushover.net/
    |
    */
    'user_key' => env('PUSHOVER_USER_KEY'),
];
