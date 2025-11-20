<?php

namespace App\Domain\Payment;

use App\Domain\Events\DomainEvent;
use App\Domain\Payment\Events\DunningEscalationTriggered;
use App\Domain\Payment\Events\DunningInitiated;
use App\Domain\Payment\Events\DunningManagementSagaCompleted;
use App\Domain\Payment\Events\DunningManagementSagaFailed;
use App\Domain\Payment\Events\DunningManagementSagaStarted;
use App\Domain\Payment\Events\DunningManagementSagaStateChanged;
use App\Domain\Payment\Events\DunningRetryScheduled;
use App\Domain\Shared\AggregateRoot;
use App\Domain\Shared\StateMachineValidator;
use App\Domain\Shared\UuidValidator;

/**
 * Event-sourced saga for dunning management (failed payment recovery).
 *
 * State Machine:
 * PENDING_RETRY
 *   ↓ (DunningRetryScheduled)
 * PENDING_PAYMENT_ATTEMPT
 *   ↓ (PaymentAttempted)
 * COMPLETED (success) OR ESCALATED (retry exhausted)
 *   ↓ (DunningEscalationTriggered)
 * FAILED (all escalations exhausted)
 */
class DunningManagementSaga extends AggregateRoot
{
    public string $uuid;
    public string $state = 'pending_retry';
    public int $subscriptionId;
    public int $userId;
    public float $amount;
    public int $attemptNumber = 0;
    public int $maxAttempts = 5;
    /** @var array<int> */
    public array $retrySchedule = [1, 3, 7, 14, 30];

    private static ?StateMachineValidator $validator = null;

    /**
     * Start a new dunning management saga.
     *
     * @param  string  $uuid
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $metadata
     *
     * @throws \InvalidArgumentException If UUID is invalid
     */
    public static function start(string $uuid, array $payload = [], array $metadata = []): self
    {
        UuidValidator::validate($uuid);

        $aggregate = new self();
        $aggregate->uuid = $uuid;

        $aggregate->recordThat(new DunningManagementSagaStarted($uuid, $payload, $metadata));

        return $aggregate;
    }

    /**
     * Get the state machine validator.
     */
    private static function getValidator(): StateMachineValidator
    {
        if (self::$validator === null) {
            self::$validator = new StateMachineValidator([
                'pending_retry' => ['pending_payment_attempt', 'failed'],
                'pending_payment_attempt' => ['completed', 'escalated', 'failed'],
                'escalated' => ['completed', 'failed'],
                'completed' => [],
                'failed' => [],
            ]);
        }

        return self::$validator;
    }

    /**
     * Transition to next state.
     *
     * @throws \InvalidArgumentException If transition is invalid
     */
    public function transitionTo(string $newState, string $eventType, array $payload = []): void
    {
        self::getValidator()->validate($this->state, $newState);

        $this->recordThat(new DunningManagementSagaStateChanged(
            $this->uuid,
            ['from_state' => $this->state, 'to_state' => $newState, ...$payload],
            ['event_type' => $eventType]
        ));
    }

    /**
     * Record dunning initiation.
     */
    public function initiateDunning(array $payload = []): void
    {
        $this->recordThat(new DunningInitiated($this->uuid, $payload, []));
    }

    /**
     * Schedule a retry.
     */
    public function scheduleRetry(array $payload = []): void
    {
        $this->recordThat(new DunningRetryScheduled($this->uuid, $payload, []));
    }

    /**
     * Trigger escalation.
     */
    public function triggerEscalation(array $payload = []): void
    {
        $this->recordThat(new DunningEscalationTriggered($this->uuid, $payload, []));
    }

    /**
     * Mark saga as completed.
     */
    public function complete(array $payload = []): void
    {
        $this->recordThat(new DunningManagementSagaCompleted($this->uuid, $payload, []));
    }

    /**
     * Mark saga as failed.
     */
    public function fail(array $payload = []): void
    {
        $this->recordThat(new DunningManagementSagaFailed($this->uuid, $payload, []));
    }

    protected function apply(DomainEvent $event): void
    {
        if ($event instanceof DunningManagementSagaStarted) {
            $this->uuid = $event->aggregateUuid;
            $this->subscriptionId = $event->payload['subscription_id'] ?? 0;
            $this->userId = $event->payload['user_id'] ?? 0;
            $this->amount = $event->payload['amount'] ?? 0;
            $this->maxAttempts = $event->payload['max_attempts'] ?? 5;
            $this->retrySchedule = $event->payload['retry_schedule'] ?? [1, 3, 7, 14, 30];
            $this->state = 'pending_retry';
        } elseif ($event instanceof DunningManagementSagaStateChanged) {
            $this->state = $event->payload['to_state'] ?? $this->state;
        } elseif ($event instanceof DunningRetryScheduled) {
            $this->attemptNumber = $event->payload['attempt_number'] ?? $this->attemptNumber;
            $this->state = 'pending_payment_attempt';
        } elseif ($event instanceof DunningEscalationTriggered) {
            $this->state = 'escalated';
        } elseif ($event instanceof DunningManagementSagaCompleted) {
            $this->state = 'completed';
        } elseif ($event instanceof DunningManagementSagaFailed) {
            $this->state = 'failed';
        }
    }
}

