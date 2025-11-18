<?php

namespace App\Domain\Order;

use App\Domain\Shared\AggregateRoot;
use App\Domain\Events\DomainEvent;
use DateTimeImmutable;

/**
 * OrderFulfillmentSaga - Orchestrates the order fulfillment process.
 *
 * Tracks saga state through multiple steps with compensation actions.
 * Implements the Saga pattern for distributed transactions.
 */
class OrderFulfillmentSaga extends AggregateRoot
{
    public string $uuid;
    public string $orderId;
    public string $state = 'PENDING_PRESCRIPTION';
    public array $compensationStack = [];
    public DateTimeImmutable $startedAt;
    public ?DateTimeImmutable $completedAt = null;

    /**
     * Start a new order fulfillment saga.
     */
    public static function start(string $sagaUuid, string $orderId, array $metadata = []): self
    {
        $saga = new self();
        $saga->recordThat(new Events\OrderFulfillmentSagaStarted(
            $sagaUuid,
            ['order_id' => $orderId],
            $metadata
        ));
        return $saga;
    }

    /**
     * Transition to next state.
     */
    public function transitionTo(string $newState, string $eventType, array $payload = []): void
    {
        $this->recordThat(new Events\OrderFulfillmentSagaStateChanged(
            $this->uuid,
            ['from_state' => $this->state, 'to_state' => $newState, ...$payload],
            ['event_type' => $eventType]
        ));
    }

    /**
     * Record a compensation action for rollback.
     */
    public function recordCompensation(string $compensationAction, array $data = []): void
    {
        $this->compensationStack[] = ['action' => $compensationAction, 'data' => $data];
        $this->recordThat(new Events\CompensationRecorded(
            $this->uuid,
            ['action' => $compensationAction, 'data' => $data],
            []
        ));
    }

    /**
     * Mark saga as failed and trigger compensation.
     */
    public function fail(string $reason, string $failedStep): void
    {
        $this->recordThat(new Events\OrderFulfillmentSagaFailed(
            $this->uuid,
            ['reason' => $reason, 'failed_step' => $failedStep, 'compensation_stack' => $this->compensationStack],
            []
        ));
    }

    /**
     * Mark saga as completed.
     */
    public function complete(): void
    {
        $this->recordThat(new Events\OrderFulfillmentSagaCompleted(
            $this->uuid,
            ['completed_at' => now()->toIso8601String()],
            []
        ));
    }

    /**
     * Apply domain events to update state.
     */
    public function apply(DomainEvent $event): void
    {
        match ($event::eventType()) {
            Events\OrderFulfillmentSagaStarted::eventType() => $this->applyStarted($event),
            Events\OrderFulfillmentSagaStateChanged::eventType() => $this->applyStateChanged($event),
            Events\CompensationRecorded::eventType() => $this->applyCompensationRecorded($event),
            Events\OrderFulfillmentSagaFailed::eventType() => $this->applyFailed($event),
            Events\OrderFulfillmentSagaCompleted::eventType() => $this->applyCompleted($event),
            default => null,
        };
    }

    private function applyStarted(DomainEvent $event): void
    {
        $this->uuid = $event->aggregateUuid;
        $this->orderId = $event->payload['order_id'];
        $this->state = 'PENDING_PRESCRIPTION';
        $this->startedAt = $event->occurredAt;
    }

    private function applyStateChanged(DomainEvent $event): void
    {
        $this->state = $event->payload['to_state'];
    }

    private function applyCompensationRecorded(DomainEvent $event): void
    {
        $this->compensationStack[] = [
            'action' => $event->payload['action'],
            'data' => $event->payload['data'],
        ];
    }

    private function applyFailed(DomainEvent $event): void
    {
        $this->state = 'FAILED';
        $this->compensationStack = $event->payload['compensation_stack'] ?? [];
    }

    private function applyCompleted(DomainEvent $event): void
    {
        $this->state = 'COMPLETED';
        $this->completedAt = $event->occurredAt;
    }

    public static function aggregateType(): string
    {
        return 'order_fulfillment_saga';
    }
}

