<?php

namespace App\Domain\Payment\Events;

use App\Domain\Events\DomainEvent;
use App\Domain\Shared\PayloadValidator;

/**
 * Domain event raised when a dunning retry is scheduled.
 *
 * Payload should include:
 * - subscription_id: The subscription being retried
 * - user_id: The user being retried
 * - attempt_number: Which attempt this is (1-5)
 * - scheduled_for: When the retry is scheduled
 * - days_until_retry: Days until the retry
 * - notification_channels: Channels to use (email, sms, phone)
 */
class DunningRetryScheduled extends DomainEvent
{
    public function __construct(string $aggregateUuid, array $payload = [], array $metadata = [])
    {
        // Validate required fields
        PayloadValidator::validateRequired($payload, [
            'subscription_id',
            'user_id',
            'attempt_number',
            'days_until_retry',
        ]);

        // Validate field types
        PayloadValidator::validateType($payload, 'subscription_id', 'int');
        PayloadValidator::validateType($payload, 'user_id', 'int');
        PayloadValidator::validateType($payload, 'attempt_number', 'int');
        PayloadValidator::validateType($payload, 'days_until_retry', 'int');
        PayloadValidator::validateType($payload, 'notification_channels', 'array');

        // Validate ranges
        PayloadValidator::validateRange($payload, 'attempt_number', 1, 5);
        PayloadValidator::validateRange($payload, 'days_until_retry', 1, 30);

        parent::__construct($aggregateUuid, $payload, $metadata);
    }

    public static function aggregateType(): string
    {
        return 'dunning_management_saga';
    }

    public static function eventType(): string
    {
        return 'dunning_management_saga.retry_scheduled';
    }
}

