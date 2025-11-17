<?php

namespace App\Services;

use App\Domain\Events\DomainEvent;

/**
 * Central registry for projection replay metadata.
 *
 * Backed by the projection_replay config, it knows:
 * - which DomainEvent class belongs to a given event_type string; and
 * - which event types are associated with a given logical projection name.
 */
class ProjectionRegistry
{
    /**
     * @param  array<string, string|array{class: class-string<DomainEvent>}>|null  $eventTypeMap
     * @param  array<string, mixed>|null                                           $projectionMap
     */
    public function __construct(
        private ?array $eventTypeMap = null,
        private ?array $projectionMap = null,
    ) {
        $this->eventTypeMap ??= (array) config('projection_replay.event_types', []);
        $this->projectionMap ??= (array) config('projection_replay.projections', []);
    }

    /**
     * Return the concrete DomainEvent class for a logical event_type string.
     */
    public function eventClassFor(string $eventType): ?string
    {
        $definition = $this->eventTypeMap[$eventType] ?? null;

        if (is_string($definition)) {
            return $definition;
        }

        if (is_array($definition) && isset($definition['class']) && is_string($definition['class'])) {
            return $definition['class'];
        }

        return null;
    }

    /**
     * Return the event_type strings associated with a projection name.
     *
     * @return array<int, string>
     */
    public function eventTypesForProjection(string $projection): array
    {
        $definition = $this->projectionMap[$projection] ?? null;

        if ($definition === null) {
            return [];
        }

        // Support the simple "list of event_type strings" shape:
        if (is_array($definition) && array_is_list($definition)) {
            /** @var array<int, string> $definition */
            return $definition;
        }

        // Support richer config shapes where event types are nested:
        if (is_array($definition)) {
            if (isset($definition['event_types']) && is_array($definition['event_types'])) {
                /** @var array<int, string> $eventTypes */
                $eventTypes = array_values($definition['event_types']);

                return $eventTypes;
            }

            if (isset($definition['events']) && is_array($definition['events'])) {
                // If "events" is an associative array keyed by event_type
                if (! array_is_list($definition['events'])) {
                    /** @var array<string, mixed> $events */
                    $events = $definition['events'];

                    return array_keys($events);
                }

                /** @var array<int, string> $events */
                $events = $definition['events'];

                return $events;
            }
        }

        return [];
    }

    /**
     * All known projection names.
     *
     * @return array<int, string>
     */
    public function projectionNames(): array
    {
        return array_keys($this->projectionMap);
    }

    public function hasProjection(string $projection): bool
    {
        return array_key_exists($projection, $this->projectionMap);
    }

    /**
     * Get the raw config definition for a projection.
     *
     * @return array<string, mixed>|null
     */
    public function projectionDefinition(string $projection): ?array
    {
        $definition = $this->projectionMap[$projection] ?? null;

        return is_array($definition) ? $definition : null;
    }
}

