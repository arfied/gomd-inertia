<?php

namespace Tests\Unit\Domain\Subscription;

use App\Domain\Subscription\Events\PaymentAttempted;
use App\Domain\Subscription\Events\PaymentFailed;
use App\Domain\Subscription\Events\SubscriptionRenewalSagaCompleted;
use App\Domain\Subscription\Events\SubscriptionRenewalSagaFailed;
use App\Domain\Subscription\Events\SubscriptionRenewalSagaStarted;
use App\Domain\Subscription\Events\SubscriptionRenewalSagaStateChanged;
use App\Domain\Subscription\SubscriptionRenewalSaga;
use PHPUnit\Framework\TestCase;

class SubscriptionRenewalSagaTest extends TestCase
{
    private string $sagaUuid = '550e8400-e29b-41d4-a716-446655440002';

    public function test_start_subscription_renewal_saga(): void
    {
        $payload = [
            'subscription_id' => 1,
            'user_id' => 1,
            'plan_id' => 1,
            'amount' => 99.99,
            'billing_date' => now()->toDateString(),
        ];

        $saga = SubscriptionRenewalSaga::start($this->sagaUuid, $payload);

        $this->assertEquals($this->sagaUuid, $saga->uuid);
        $this->assertEquals('pending_payment_method_check', $saga->state);
        $this->assertEquals(1, $saga->subscriptionId);
        $this->assertEquals(1, $saga->userId);
        $this->assertEquals(99.99, $saga->amount);
        $this->assertCount(1, $saga->getRecordedEvents());
        $this->assertInstanceOf(SubscriptionRenewalSagaStarted::class, $saga->getRecordedEvents()[0]);
    }

    public function test_transition_to_pending_payment_attempt(): void
    {
        $payload = ['subscription_id' => 1, 'user_id' => 1, 'amount' => 99.99];
        $saga = SubscriptionRenewalSaga::start($this->sagaUuid, $payload);

        $saga->transitionTo('pending_payment_attempt', 'payment_method_checked');

        $this->assertEquals('pending_payment_attempt', $saga->state);
        $this->assertCount(2, $saga->getRecordedEvents());
        $this->assertInstanceOf(SubscriptionRenewalSagaStateChanged::class, $saga->getRecordedEvents()[1]);
    }

    public function test_record_payment_attempt(): void
    {
        $payload = ['subscription_id' => 1, 'user_id' => 1, 'amount' => 99.99];
        $saga = SubscriptionRenewalSaga::start($this->sagaUuid, $payload);

        $attemptPayload = [
            'subscription_id' => 1,
            'payment_method_id' => 1,
            'amount' => 99.99,
            'attempt_number' => 1,
            'transaction_id' => 'txn_123',
        ];
        $saga->recordPaymentAttempt($attemptPayload);

        $this->assertEquals(1, $saga->attemptNumber);
        $this->assertCount(2, $saga->getRecordedEvents());
        $this->assertInstanceOf(PaymentAttempted::class, $saga->getRecordedEvents()[1]);
    }

    public function test_record_payment_failure(): void
    {
        $payload = ['subscription_id' => 1, 'user_id' => 1, 'amount' => 99.99];
        $saga = SubscriptionRenewalSaga::start($this->sagaUuid, $payload);

        $failurePayload = [
            'subscription_id' => 1,
            'payment_method_id' => 1,
            'amount' => 99.99,
            'attempt_number' => 1,
            'error_code' => 'INSUFFICIENT_FUNDS',
            'error_message' => 'Insufficient funds',
        ];
        $saga->recordPaymentFailure($failurePayload);

        $this->assertCount(2, $saga->getRecordedEvents());
        $this->assertInstanceOf(PaymentFailed::class, $saga->getRecordedEvents()[1]);
    }

    public function test_complete_saga(): void
    {
        $payload = ['subscription_id' => 1, 'user_id' => 1, 'amount' => 99.99];
        $saga = SubscriptionRenewalSaga::start($this->sagaUuid, $payload);

        $completionPayload = [
            'transaction_id' => 'txn_123',
            'renewed_at' => now()->toDateTimeString(),
            'next_billing_date' => now()->addMonth()->toDateString(),
        ];
        $saga->complete($completionPayload);

        $this->assertEquals('completed', $saga->state);
        $this->assertCount(2, $saga->getRecordedEvents());
        $this->assertInstanceOf(SubscriptionRenewalSagaCompleted::class, $saga->getRecordedEvents()[1]);
    }

    public function test_fail_saga(): void
    {
        $payload = ['subscription_id' => 1, 'user_id' => 1, 'amount' => 99.99];
        $saga = SubscriptionRenewalSaga::start($this->sagaUuid, $payload);

        $failurePayload = [
            'reason' => 'All payment attempts failed',
            'failed_at' => now()->toDateTimeString(),
        ];
        $saga->fail($failurePayload);

        $this->assertEquals('failed', $saga->state);
        $this->assertCount(2, $saga->getRecordedEvents());
        $this->assertInstanceOf(SubscriptionRenewalSagaFailed::class, $saga->getRecordedEvents()[1]);
    }

    public function test_reconstitute_from_history(): void
    {
        $events = [
            new SubscriptionRenewalSagaStarted($this->sagaUuid, [
                'subscription_id' => 1,
                'user_id' => 1,
                'amount' => 99.99,
            ]),
            new PaymentAttempted($this->sagaUuid, [
                'subscription_id' => 1,
                'payment_method_id' => 1,
                'amount' => 99.99,
                'attempt_number' => 1,
            ]),
            new SubscriptionRenewalSagaCompleted($this->sagaUuid, ['transaction_id' => 'txn_123']),
        ];

        $saga = SubscriptionRenewalSaga::reconstituteFromHistory($events);

        $this->assertEquals($this->sagaUuid, $saga->uuid);
        $this->assertEquals('completed', $saga->state);
        $this->assertEquals(1, $saga->attemptNumber);
    }

    public function test_event_types(): void
    {
        $event = new SubscriptionRenewalSagaStarted($this->sagaUuid);
        $this->assertEquals('subscription_renewal_saga', $event->aggregateType());
        $this->assertEquals('subscription_renewal_saga.started', $event->eventType());
    }

    public function test_valid_state_transitions(): void
    {
        $payload = ['subscription_id' => 1, 'user_id' => 1, 'amount' => 99.99];
        $saga = SubscriptionRenewalSaga::start($this->sagaUuid, $payload);

        // Valid: pending_payment_method_check -> pending_payment_attempt
        $saga->transitionTo('pending_payment_attempt', 'payment_method_checked');
        $this->assertEquals('pending_payment_attempt', $saga->state);

        // Valid: pending_payment_attempt -> completed
        $saga->transitionTo('completed', 'payment_succeeded');
        $this->assertEquals('completed', $saga->state);
    }

    public function test_invalid_state_transition_throws_exception(): void
    {
        $payload = ['subscription_id' => 1, 'user_id' => 1, 'amount' => 99.99];
        $saga = SubscriptionRenewalSaga::start($this->sagaUuid, $payload);

        // Invalid: pending_payment_method_check -> completed (must go through pending_payment_attempt)
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid transition from 'pending_payment_method_check' to 'completed'");

        $saga->transitionTo('completed', 'invalid_transition');
    }

    public function test_terminal_state_cannot_transition(): void
    {
        $payload = ['subscription_id' => 1, 'user_id' => 1, 'amount' => 99.99];
        $saga = SubscriptionRenewalSaga::start($this->sagaUuid, $payload);

        $saga->complete(['transaction_id' => 'txn_123']);
        $this->assertEquals('completed', $saga->state);

        // Invalid: completed is terminal state
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid transition from 'completed' to 'failed'");

        $saga->transitionTo('failed', 'invalid_transition');
    }
}

