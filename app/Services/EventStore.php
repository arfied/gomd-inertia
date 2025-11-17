<?php

namespace App\Services;

use App\Domain\Events\DomainEvent;
use App\Models\StoredEvent;

class EventStore implements EventStoreContract
{
    public function __construct(private ?EventStoreMonitor $monitor = null)
    {
    }

    /**
     * Persist a domain event and return the stored record.
     */
    public function store(DomainEvent $event): StoredEvent
    {
        $stored = StoredEvent::create($event->toStoredEventAttributes());

        if ($this->monitor !== null) {
            $this->monitor->recordStored($event, $stored);
        }

        return $stored;
    }
}

