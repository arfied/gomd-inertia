<?php

namespace App\Domain\Payment;

use App\Domain\Events\DomainEvent;
use App\Domain\Payment\Events\PaymentMethodAdded;
use App\Domain\Payment\Events\PaymentMethodRemoved;
use App\Domain\Payment\Events\PaymentMethodSetAsDefault;
use App\Domain\Payment\Events\PaymentMethodUpdated;
use App\Domain\Shared\AggregateRoot;

/**
 * Event-sourced aggregate for the Payment Method bounded context.
 */
class PaymentMethodAggregate extends AggregateRoot
{
    public string $uuid;
    public string $type;
    public bool $isDefault = false;
    public bool $isRemoved = false;

    /**
     * Add a new payment method and record a PaymentMethodAdded event.
     *
     * @param  string  $uuid
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $metadata
     */
    public static function add(string $uuid, array $payload = [], array $metadata = []): self
    {
        $aggregate = new self();
        $aggregate->uuid = $uuid;

        $aggregate->recordThat(new PaymentMethodAdded($uuid, $payload, $metadata));

        return $aggregate;
    }

    /**
     * Update a payment method and record a PaymentMethodUpdated event.
     *
     * @param  string  $uuid
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $metadata
     */
    public static function update(string $uuid, array $payload = [], array $metadata = []): self
    {
        $aggregate = new self();
        $aggregate->uuid = $uuid;

        $aggregate->recordThat(new PaymentMethodUpdated($uuid, $payload, $metadata));

        return $aggregate;
    }

    /**
     * Remove a payment method and record a PaymentMethodRemoved event.
     *
     * @param  string  $uuid
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $metadata
     */
    public static function remove(string $uuid, array $payload = [], array $metadata = []): self
    {
        $aggregate = new self();
        $aggregate->uuid = $uuid;

        $aggregate->recordThat(new PaymentMethodRemoved($uuid, $payload, $metadata));

        return $aggregate;
    }

    /**
     * Set a payment method as default and record a PaymentMethodSetAsDefault event.
     *
     * @param  string  $uuid
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $metadata
     */
    public static function setAsDefault(string $uuid, array $payload = [], array $metadata = []): self
    {
        $aggregate = new self();
        $aggregate->uuid = $uuid;

        $aggregate->recordThat(new PaymentMethodSetAsDefault($uuid, $payload, $metadata));

        return $aggregate;
    }

    protected function apply(DomainEvent $event): void
    {
        if ($event instanceof PaymentMethodAdded) {
            $this->uuid = $event->aggregateUuid;
            $this->type = $event->payload['type'] ?? 'unknown';
            $this->isDefault = $event->payload['is_default'] ?? false;
            $this->isRemoved = false;
        } elseif ($event instanceof PaymentMethodUpdated) {
            $this->uuid = $event->aggregateUuid;
        } elseif ($event instanceof PaymentMethodRemoved) {
            $this->uuid = $event->aggregateUuid;
            $this->isRemoved = true;
        } elseif ($event instanceof PaymentMethodSetAsDefault) {
            $this->uuid = $event->aggregateUuid;
            $this->isDefault = true;
        }
    }
}

