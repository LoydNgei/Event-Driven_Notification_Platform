<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Notification Platform Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for the notification platform,
    | including retry settings, channel defaults, and feature flags.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Retry Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how failed notifications are retried.
    |
    */

    'retries' => [
        'max_attempts' => (int) env('NOTIFICATION_MAX_ATTEMPTS', 3),
        'backoff' => [60, 300, 900], // seconds: 1min, 5min, 15min
    ],

    /*
    |--------------------------------------------------------------------------
    | Channel Configuration
    |--------------------------------------------------------------------------
    |
    | Default settings for each notification channel.
    |
    */

    'channels' => [
        'email' => [
            'enabled' => env('NOTIFICATION_EMAIL_ENABLED', true),
            'from_address' => env('MAIL_FROM_ADDRESS', 'noreply@example.com'),
            'from_name' => env('MAIL_FROM_NAME', 'Notification Platform'),
        ],

        'sms' => [
            'enabled' => env('NOTIFICATION_SMS_ENABLED', true),
            'driver' => env('SMS_DRIVER', 'log'), // log, twilio, vonage
        ],

        'slack' => [
            'enabled' => env('NOTIFICATION_SLACK_ENABLED', true),
            'default_webhook' => env('SLACK_DEFAULT_WEBHOOK'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Control notification event logging.
    |
    */

    'logging' => [
        'enabled' => env('NOTIFICATION_LOGGING_ENABLED', true),
        'retention_days' => (int) env('NOTIFICATION_LOG_RETENTION_DAYS', 30),
    ],

];
