<?php

namespace App\Domain\Compliance\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when access to patient data is logged.
 *
 * Payload includes: patient_id, accessed_by, access_type, resource, accessed_at, ip_address, user_agent.
 */
class AccessLogged extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'audit_log';
    }

    public static function eventType(): string
    {
        return 'audit_log.access_logged';
    }
}

