<?php

namespace App\Services;

use App\Domain\Events\DomainEvent;
use App\Models\StoredEvent;

/**
 * Contract for persisting domain events.
 */
interface EventStoreContract
{
    /**
     * Persist a domain event and return the stored record.
     */
    public function store(DomainEvent $event): StoredEvent;
}

