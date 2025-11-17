<?php

namespace App\Domain\Order;

use App\Domain\Events\DomainEvent;
use App\Domain\Order\Events\OrderCancelled;
use App\Domain\Order\Events\OrderCreated;
use App\Domain\Order\Events\OrderFulfilled;
use App\Domain\Order\Events\OrderAssignedToDoctor;
use App\Domain\Shared\AggregateRoot;

/**
 * Event-sourced aggregate for the Order bounded context.
 */
class OrderAggregate extends AggregateRoot
{
    public string $uuid;

    /**
     * Create a new order aggregate and record an OrderCreated event.
     *
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $metadata
     */
    public static function create(string $uuid, array $payload = [], array $metadata = []): self
    {
        $aggregate = new self();
        $aggregate->uuid = $uuid;

        $aggregate->recordThat(new OrderCreated($uuid, $payload, $metadata));

        return $aggregate;
    }
    /**
     * Record an OrderAssignedToDoctor event for the given aggregate UUID.
     *
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $metadata
     */
    public static function assignDoctor(string $uuid, array $payload = [], array $metadata = []): self
    {
        $aggregate = new self();
        $aggregate->uuid = $uuid;

        $aggregate->recordThat(new OrderAssignedToDoctor($uuid, $payload, $metadata));

        return $aggregate;
    }



    /**
     * Record an OrderFulfilled event for the given aggregate UUID.
     *
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $metadata
     */
    public static function fulfill(string $uuid, array $payload = [], array $metadata = []): self
    {
        $aggregate = new self();
        $aggregate->uuid = $uuid;

        $aggregate->recordThat(new OrderFulfilled($uuid, $payload, $metadata));

        return $aggregate;
    }

    /**
     * Record an OrderCancelled event for the given aggregate UUID.
     *
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $metadata
     */
    public static function cancel(string $uuid, array $payload = [], array $metadata = []): self
    {
        $aggregate = new self();
        $aggregate->uuid = $uuid;

        $aggregate->recordThat(new OrderCancelled($uuid, $payload, $metadata));

        return $aggregate;
    }

    protected function apply(DomainEvent $event): void
    {
        if ($event instanceof OrderCreated
            || $event instanceof OrderFulfilled
            || $event instanceof OrderCancelled
            || $event instanceof OrderAssignedToDoctor
        ) {
            $this->uuid = $event->aggregateUuid;
        }
    }
}

