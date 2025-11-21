<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Subscription Renewal Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for subscription renewal process including idempotency,
    | retry logic, and rate limiting.
    |
    */

    'renewal' => [
        /*
        |--------------------------------------------------------------------------
        | Idempotency Cache TTL
        |--------------------------------------------------------------------------
        |
        | Number of days to keep renewal idempotency cache entries.
        | This prevents duplicate processing if a renewal job is retried.
        |
        | Recommended values:
        | - 30 days: Standard retention for most use cases
        | - 60 days: Extended retention for high-volume systems
        | - 90 days: Maximum retention for compliance requirements
        |
        */
        'idempotency_ttl_days' => (int) env('RENEWAL_IDEMPOTENCY_TTL_DAYS', 30),

        /*
        |--------------------------------------------------------------------------
        | Maximum Retry Attempts
        |--------------------------------------------------------------------------
        |
        | Maximum number of times to retry a failed renewal payment.
        |
        */
        'max_attempts' => (int) env('RENEWAL_MAX_ATTEMPTS', 5),

        /*
        |--------------------------------------------------------------------------
        | Retry Schedule (in days)
        |--------------------------------------------------------------------------
        |
        | Delay between retry attempts in days.
        | Must have at least max_attempts - 1 entries.
        |
        | Example: [1, 3, 7, 14, 30] means:
        | - 1st retry: 1 day after initial failure
        | - 2nd retry: 3 days after 1st retry
        | - 3rd retry: 7 days after 2nd retry
        | - 4th retry: 14 days after 3rd retry
        | - 5th retry: 30 days after 4th retry
        |
        */
        'retry_schedule' => [1, 3, 7, 14, 30],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Configuration
    |--------------------------------------------------------------------------
    |
    | Rate limits for subscription renewal endpoints.
    |
    */
    'rate_limiting' => [
        'hourly_limit' => (int) env('RENEWAL_HOURLY_LIMIT', 5),
        'daily_limit' => (int) env('RENEWAL_DAILY_LIMIT', 20),
    ],

    /*
    |--------------------------------------------------------------------------
    | Failure Alert Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for renewal failure alerts and notifications.
    |
    */
    'failure_alerts' => [
        'enabled' => (bool) env('RENEWAL_FAILURE_ALERTS_ENABLED', true),
        'email_recipients' => explode(',', env('RENEWAL_FAILURE_EMAIL_RECIPIENTS', '')),
        'slack_webhook' => env('RENEWAL_FAILURE_SLACK_WEBHOOK', null),
        'pagerduty_key' => env('RENEWAL_FAILURE_PAGERDUTY_KEY', null),
    ],
];

