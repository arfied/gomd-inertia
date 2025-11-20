<?php

namespace App\Domain\Subscription\Events;

use App\Domain\Events\DomainEvent;
use App\Domain\Shared\PayloadValidator;

/**
 * Domain event raised when a payment attempt fails.
 *
 * Payload should include:
 * - subscription_id: The subscription with failed payment
 * - payment_method_id: The payment method that failed
 * - amount: The amount that failed to charge
 * - attempt_number: Which attempt failed (1-5)
 * - error_code: Error code from payment processor
 * - error_message: Error message from payment processor
 * - failed_at: When the failure occurred
 * - next_retry_date: When to retry (if applicable)
 */
class PaymentFailed extends DomainEvent
{
    public function __construct(string $aggregateUuid, array $payload = [], array $metadata = [])
    {
        // Validate required fields
        PayloadValidator::validateRequired($payload, [
            'subscription_id',
            'payment_method_id',
            'amount',
            'attempt_number',
            'error_code',
        ]);

        // Validate field types
        PayloadValidator::validateType($payload, 'subscription_id', 'int');
        PayloadValidator::validateType($payload, 'payment_method_id', 'int');
        PayloadValidator::validateType($payload, 'amount', 'float');
        PayloadValidator::validateType($payload, 'attempt_number', 'int');
        PayloadValidator::validateType($payload, 'error_code', 'string');

        // Validate ranges
        PayloadValidator::validateRange($payload, 'attempt_number', 1, 5);
        PayloadValidator::validateRange($payload, 'amount', 0.01, 999999.99);

        parent::__construct($aggregateUuid, $payload, $metadata);
    }

    public static function aggregateType(): string
    {
        return 'subscription_renewal_saga';
    }

    public static function eventType(): string
    {
        return 'subscription_renewal_saga.payment_failed';
    }
}

