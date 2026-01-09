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
        'registration_confirmation' => 'Hallo :first_name! Deine Anmeldung für ":event_title" am :event_date wurde bestätigt. Wir freuen uns auf dich! - :site_name',

        'event_reminder' => 'Erinnerung: ":event_title" findet morgen am :event_date um :start_time statt. Ort: :location. Bis bald! - :site_name',
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
