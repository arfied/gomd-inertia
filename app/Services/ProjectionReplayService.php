<?php

namespace App\Services;

use App\Domain\Events\DomainEvent;
use App\Models\StoredEvent;
use Illuminate\Contracts\Events\Dispatcher;

class ProjectionReplayService
{
    public function __construct(
        private Dispatcher $dispatcher,
        private ProjectionRegistry $registry,
        private int $defaultBatchSize = 100,
    ) {
    }

    /**
     * Replay events from the event store according to the provided options.
     *
     * @param  callable(string): void|null  $output
     */
    public function replay(ProjectionReplayOptions $options, ?callable $output = null): ProjectionReplayResult
    {
        $result = new ProjectionReplayResult();

        $batchSize = $options->batchSize > 0 ? $options->batchSize : $this->defaultBatchSize;

        $query = StoredEvent::query()->orderBy('id');

        if ($options->aggregateType !== null) {
            $query->where('aggregate_type', $options->aggregateType);
        }

        if ($options->fromId !== null) {
            $query->where('id', '>=', $options->fromId);
        }

        if ($options->toId !== null) {
            $query->where('id', '<=', $options->toId);
        }

        if ($options->projection !== null) {
            $eventTypes = $this->registry->eventTypesForProjection($options->projection);

            if ($eventTypes === []) {
                throw new \InvalidArgumentException("Unknown projection [{$options->projection}]");
            }

            $query->whereIn('event_type', $eventTypes);
        }

        $query->chunkById($batchSize, function ($storedEvents) use ($options, &$result, $output): void {
            $result->batches++;

            foreach ($storedEvents as $storedEvent) {
                $result->eventsProcessed++;

                $event = $this->rehydrateDomainEvent($storedEvent);

                if (! $event) {
                    if ($output !== null) {
                        $output("Skipping event #{$storedEvent->id} of type [{$storedEvent->event_type}] (no mapping)");
                    }

                    continue;
                }

                if ($options->dryRun) {
                    if ($output !== null) {
                        $output("DRY RUN: would dispatch [".get_class($event)."] for event #{$storedEvent->id}");
                    }

                    continue;
                }

                $this->dispatcher->dispatch($event);
                $result->eventsDispatched++;

                if ($output !== null) {
                    $output("Dispatched [".get_class($event)."] for event #{$storedEvent->id}");
                }
            }
        });

        return $result;
    }

    /**
     * Known projection names.
     *
     * @return array<int, string>
     */
    public function knownProjections(): array
    {
        return $this->registry->projectionNames();
    }

    private function rehydrateDomainEvent(StoredEvent $storedEvent): ?DomainEvent
    {
        $class = $this->registry->eventClassFor($storedEvent->event_type);

        if ($class === null || ! is_subclass_of($class, DomainEvent::class)) {
            return null;
        }

        /** @var DomainEvent $event */
        $event = new $class(
            $storedEvent->aggregate_uuid,
            $storedEvent->event_data ?? [],
            $storedEvent->metadata ?? [],
        );

        if ($storedEvent->occurred_at instanceof \DateTimeInterface) {
            $event->occurredAt = \DateTimeImmutable::createFromInterface($storedEvent->occurred_at);
        }

        return $event;
    }
}

