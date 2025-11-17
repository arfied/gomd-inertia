<?php

namespace App\Services;

use App\Domain\Events\DomainEvent;
use App\Domain\Patient\Events\PatientEnrolled;
use App\Models\StoredEvent;
use Illuminate\Contracts\Events\Dispatcher;

class ProjectionReplayService
{
    /**
     * Map of logical event_type strings to concrete DomainEvent classes.
     *
     * @var array<string, class-string<DomainEvent>>
     */
    private array $eventTypeMap;

    /**
     * Map of projection names to the event types they care about.
     *
     * @var array<string, array<int, string>>
     */
    private array $projectionEventTypeMap;

    public function __construct(
        private Dispatcher $dispatcher,
        ?array $eventTypeMap = null,
        ?array $projectionEventTypeMap = null,
        private int $defaultBatchSize = 100,
    ) {
        $this->eventTypeMap = $eventTypeMap ?? (array) config('projection_replay.event_types', [
            'patient.enrolled' => PatientEnrolled::class,
        ]);

        $this->projectionEventTypeMap = $projectionEventTypeMap ?? (array) config('projection_replay.projections', [
            'patient-enrollment' => ['patient.enrolled'],
            'patient-activity' => ['patient.enrolled'],
        ]);
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
            $eventTypes = $this->projectionEventTypeMap[$options->projection] ?? null;

            if ($eventTypes === null) {
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
        return array_keys($this->projectionEventTypeMap);
    }

    private function rehydrateDomainEvent(StoredEvent $storedEvent): ?DomainEvent
    {
        $class = $this->eventTypeMap[$storedEvent->event_type] ?? null;

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

