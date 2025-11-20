<?php

namespace App\Domain\Subscription\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when subscription renewal saga fails.
 *
 * Payload should include:
 * - subscription_id: The subscription that failed to renew
 * - reason: Why the renewal failed
 * - error_message: Detailed error message
 * - failed_at: When the failure occurred
 */
class SubscriptionRenewalSagaFailed extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'subscription_renewal_saga';
    }

    public static function eventType(): string
    {
        return 'subscription_renewal_saga.failed';
    }
}

