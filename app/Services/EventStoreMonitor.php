<?php

namespace App\Services;

use App\Domain\Events\DomainEvent;
use App\Models\StoredEvent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class EventStoreMonitor
{
    /**
     * Record metrics and logs when an event is stored.
     */
    public function recordStored(DomainEvent $event, StoredEvent $storedEvent): void
    {
        Cache::increment('metrics.event_store.events_stored');
        Cache::increment('metrics.event_store.events_stored.by_type.' . $storedEvent->event_type);

        Log::info('Event store: event persisted', [
            'stored_event_id' => $storedEvent->id,
            'aggregate_type' => $storedEvent->aggregate_type,
            'aggregate_uuid' => $storedEvent->aggregate_uuid,
            'event_type' => $storedEvent->event_type,
            'occurred_at' => optional($storedEvent->occurred_at)->toIso8601String(),
        ]);
    }
}

