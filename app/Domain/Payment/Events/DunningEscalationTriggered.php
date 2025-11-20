<?php

namespace App\Domain\Payment\Events;

use App\Domain\Events\DomainEvent;
use App\Domain\Shared\PayloadValidator;

/**
 * Domain event raised when dunning escalation is triggered.
 *
 * Payload should include:
 * - subscription_id: The subscription being escalated
 * - user_id: The user being escalated
 * - attempt_number: Which attempt triggered escalation
 * - escalation_level: The escalation level (1-4)
 * - actions: Actions to take (email, sms, phone, pause_service, cancel)
 * - triggered_at: When escalation was triggered
 */
class DunningEscalationTriggered extends DomainEvent
{
    public function __construct(string $aggregateUuid, array $payload = [], array $metadata = [])
    {
        // Validate required fields
        PayloadValidator::validateRequired($payload, [
            'subscription_id',
            'user_id',
            'attempt_number',
            'escalation_level',
            'actions',
        ]);

        // Validate field types
        PayloadValidator::validateType($payload, 'subscription_id', 'int');
        PayloadValidator::validateType($payload, 'user_id', 'int');
        PayloadValidator::validateType($payload, 'attempt_number', 'int');
        PayloadValidator::validateType($payload, 'escalation_level', 'int');
        PayloadValidator::validateType($payload, 'actions', 'array');

        // Validate ranges
        PayloadValidator::validateRange($payload, 'attempt_number', 1, 5);
        PayloadValidator::validateRange($payload, 'escalation_level', 1, 4);

        parent::__construct($aggregateUuid, $payload, $metadata);
    }

    public static function aggregateType(): string
    {
        return 'dunning_management_saga';
    }

    public static function eventType(): string
    {
        return 'dunning_management_saga.escalation_triggered';
    }
}

