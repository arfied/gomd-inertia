<?php

namespace App\Domain\Subscription;

use App\Domain\Events\DomainEvent;
use App\Domain\Shared\AggregateRoot;
use App\Domain\Shared\StateMachineValidator;
use App\Domain\Shared\UuidValidator;
use App\Domain\Subscription\Events\PaymentAttempted;
use App\Domain\Subscription\Events\PaymentFailed;
use App\Domain\Subscription\Events\SubscriptionRenewalSagaCompleted;
use App\Domain\Subscription\Events\SubscriptionRenewalSagaFailed;
use App\Domain\Subscription\Events\SubscriptionRenewalSagaStarted;
use App\Domain\Subscription\Events\SubscriptionRenewalSagaStateChanged;

/**
 * Event-sourced saga for subscription renewal process.
 *
 * State Machine:
 * PENDING_PAYMENT_METHOD_CHECK
 *   ↓ (PaymentMethodChecked)
 * PENDING_PAYMENT_ATTEMPT
 *   ↓ (PaymentAttempted)
 * COMPLETED (success) OR FAILED (all retries exhausted)
 */
class SubscriptionRenewalSaga extends AggregateRoot
{
    public string $uuid;
    public string $state = 'pending_payment_method_check';
    public int $subscriptionId;
    public int $userId;
    public float $amount;
    public int $attemptNumber = 0;
    public int $maxAttempts = 5;
    /** @var array<int> */
    public array $retrySchedule = [1, 3, 7, 14, 30];

    private static ?StateMachineValidator $validator = null;

    /**
     * Start a new subscription renewal saga.
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

        $aggregate->recordThat(new SubscriptionRenewalSagaStarted($uuid, $payload, $metadata));

        return $aggregate;
    }

    /**
     * Get the state machine validator.
     */
    private static function getValidator(): StateMachineValidator
    {
        if (self::$validator === null) {
            self::$validator = new StateMachineValidator([
                'pending_payment_method_check' => ['pending_payment_attempt', 'failed'],
                'pending_payment_attempt' => ['completed', 'failed'],
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

        $this->recordThat(new SubscriptionRenewalSagaStateChanged(
            $this->uuid,
            ['from_state' => $this->state, 'to_state' => $newState, ...$payload],
            ['event_type' => $eventType]
        ));
    }

    /**
     * Record a payment attempt.
     */
    public function recordPaymentAttempt(array $payload = []): void
    {
        $this->recordThat(new PaymentAttempted($this->uuid, $payload, []));
    }

    /**
     * Record a payment failure.
     */
    public function recordPaymentFailure(array $payload = []): void
    {
        $this->recordThat(new PaymentFailed($this->uuid, $payload, []));
    }

    /**
     * Mark saga as completed.
     */
    public function complete(array $payload = []): void
    {
        $this->recordThat(new SubscriptionRenewalSagaCompleted($this->uuid, $payload, []));
    }

    /**
     * Mark saga as failed.
     */
    public function fail(array $payload = []): void
    {
        $this->recordThat(new SubscriptionRenewalSagaFailed($this->uuid, $payload, []));
    }

    protected function apply(DomainEvent $event): void
    {
        if ($event instanceof SubscriptionRenewalSagaStarted) {
            $this->uuid = $event->aggregateUuid;
            $this->subscriptionId = $event->payload['subscription_id'] ?? 0;
            $this->userId = $event->payload['user_id'] ?? 0;
            $this->amount = $event->payload['amount'] ?? 0;
            $this->state = 'pending_payment_method_check';
        } elseif ($event instanceof SubscriptionRenewalSagaStateChanged) {
            $this->state = $event->payload['to_state'] ?? $this->state;
        } elseif ($event instanceof PaymentAttempted) {
            $this->attemptNumber = $event->payload['attempt_number'] ?? $this->attemptNumber;
            $this->state = 'pending_payment_attempt';
        } elseif ($event instanceof PaymentFailed) {
            $this->attemptNumber = $event->payload['attempt_number'] ?? $this->attemptNumber;
        } elseif ($event instanceof SubscriptionRenewalSagaCompleted) {
            $this->state = 'completed';
        } elseif ($event instanceof SubscriptionRenewalSagaFailed) {
            $this->state = 'failed';
        }
    }
}

