<?php

namespace App\Domain\Subscription\Events;

use App\Domain\Events\DomainEvent;
use App\Domain\Shared\PayloadValidator;

/**
 * Domain event raised when a payment is attempted for subscription renewal.
 *
 * Payload should include:
 * - subscription_id: The subscription being renewed
 * - payment_method_id: The payment method used
 * - amount: The amount charged
 * - attempt_number: Which attempt this is (1-5)
 * - transaction_id: The transaction ID from payment processor
 * - attempted_at: When the attempt occurred
 */
class PaymentAttempted extends DomainEvent
{
    public function __construct(string $aggregateUuid, array $payload = [], array $metadata = [])
    {
        // Validate required fields
        PayloadValidator::validateRequired($payload, [
            'subscription_id',
            'payment_method_id',
            'amount',
            'attempt_number',
        ]);

        // Validate field types
        PayloadValidator::validateType($payload, 'subscription_id', 'int');
        PayloadValidator::validateType($payload, 'payment_method_id', 'int');
        PayloadValidator::validateType($payload, 'amount', 'float');
        PayloadValidator::validateType($payload, 'attempt_number', 'int');

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
        return 'subscription_renewal_saga.payment_attempted';
    }
}

