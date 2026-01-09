<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Seven.io API Key
    |--------------------------------------------------------------------------
    |
    | Your Seven.io API key for sending SMS notifications.
    | You can find this in your Seven.io dashboard.
    |
    */
    'api_key' => env('SEVEN_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Default Sender
    |--------------------------------------------------------------------------
    |
    | The default sender name/number that will appear on SMS messages.
    | Can be up to 11 alphanumeric characters or a phone number.
    |
    */
    'from' => env('SEVEN_FROM', 'Männerkreis'),

    /*
    |--------------------------------------------------------------------------
    | SMS Templates
    |--------------------------------------------------------------------------
    |
    | Template texts for different SMS notifications
    |
    */
    'templates' => [
        'registration_confirmation' => 'Hallo :first_name! ":event_title" am :event_date, :start_time in :location - Anmeldung bestätigt. Details per E-Mail. Männerkreis',

        'event_reminder' => 'Erinnerung: ":event_title" morgen am :event_date um :start_time in :location. Bis bald! - Männerkreis',
    ],

    /*
    |--------------------------------------------------------------------------
    | Event Reminder Settings
    |--------------------------------------------------------------------------
    |
    | Configure when to send event reminders
    |
    */
    'reminder' => [
        // Send reminder X hours before event
        'hours_before' => 24,
    ],
];
