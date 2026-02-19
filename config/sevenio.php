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
    // Alphanumeric sender IDs support max. 11 ASCII characters (no umlauts).
    // Use SEVEN_FROM env variable to override (e.g. "Maennerkr" for 9 chars).
    'from' => env('SEVEN_FROM', 'Maennerkr'),

    /*
    |--------------------------------------------------------------------------
    | SMS Templates
    |--------------------------------------------------------------------------
    |
    | Template texts are now hardcoded in SmsService for simplicity
    | and to ensure messages fit within single SMS length (160 chars)
    |
    */
    'templates' => [
        // Hardcoded in SmsService::buildRegistrationMessage()
        // "Hallo {Name}! Deine Anmeldung ist bestätigt. Details per E-Mail. Männerkreis"

        // Hardcoded in SmsService::buildReminderMessage()
        // "Erinnerung: Männerkreis findet morgen statt. Details per E-Mail. Bis bald!"
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
