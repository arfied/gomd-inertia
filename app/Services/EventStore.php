<?php

namespace App\Services;

use App\Domain\Events\DomainEvent;
use App\Models\StoredEvent;

class EventStore implements EventStoreContract
{
    /**
     * Persist a domain event and return the stored record.
     */
    public function store(DomainEvent $event): StoredEvent
    {
        return StoredEvent::create($event->toStoredEventAttributes());
    }
}

