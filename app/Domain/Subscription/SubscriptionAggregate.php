<?php

namespace App\Domain\Subscription;

use App\Domain\Events\DomainEvent;
use App\Domain\Shared\AggregateRoot;
use App\Domain\Subscription\Events\SubscriptionCancelled;
use App\Domain\Subscription\Events\SubscriptionCreated;
use App\Domain\Subscription\Events\SubscriptionExpired;
use App\Domain\Subscription\Events\SubscriptionRenewed;

/**
 * Event-sourced aggregate for the Subscription bounded context.
 */
class SubscriptionAggregate extends AggregateRoot
{
    public string $uuid;
    public string $status = 'active';

    /**
     * Create a new subscription aggregate and record a SubscriptionCreated event.
     *
     * @param  string  $uuid
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $metadata
     */
    public static function create(string $uuid, array $payload = [], array $metadata = []): self
    {
        $aggregate = new self();
        $aggregate->uuid = $uuid;

        $aggregate->recordThat(new SubscriptionCreated($uuid, $payload, $metadata));

        return $aggregate;
    }

    /**
     * Record a SubscriptionRenewed event for the given aggregate UUID.
     *
     * @param  string  $uuid
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $metadata
     */
    public static function renew(string $uuid, array $payload = [], array $metadata = []): self
    {
        $aggregate = new self();
        $aggregate->uuid = $uuid;

        $aggregate->recordThat(new SubscriptionRenewed($uuid, $payload, $metadata));

        return $aggregate;
    }

    /**
     * Record a SubscriptionCancelled event for the given aggregate UUID.
     *
     * @param  string  $uuid
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $metadata
     */
    public static function cancel(string $uuid, array $payload = [], array $metadata = []): self
    {
        $aggregate = new self();
        $aggregate->uuid = $uuid;

        $aggregate->recordThat(new SubscriptionCancelled($uuid, $payload, $metadata));

        return $aggregate;
    }

    /**
     * Record a SubscriptionExpired event for the given aggregate UUID.
     *
     * @param  string  $uuid
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $metadata
     */
    public static function expire(string $uuid, array $payload = [], array $metadata = []): self
    {
        $aggregate = new self();
        $aggregate->uuid = $uuid;

        $aggregate->recordThat(new SubscriptionExpired($uuid, $payload, $metadata));

        return $aggregate;
    }

    protected function apply(DomainEvent $event): void
    {
        if ($event instanceof SubscriptionCreated) {
            $this->uuid = $event->aggregateUuid;
            $this->status = 'active';
        } elseif ($event instanceof SubscriptionRenewed) {
            $this->uuid = $event->aggregateUuid;
            $this->status = 'active';
        } elseif ($event instanceof SubscriptionCancelled) {
            $this->uuid = $event->aggregateUuid;
            $this->status = 'cancelled';
        } elseif ($event instanceof SubscriptionExpired) {
            $this->uuid = $event->aggregateUuid;
            $this->status = 'expired';
        }
    }
}

