<?php

namespace Tests\Unit\Domain\Payment;

use App\Domain\Payment\DunningManagementSaga;
use App\Domain\Payment\Events\DunningEscalationTriggered;
use App\Domain\Payment\Events\DunningInitiated;
use App\Domain\Payment\Events\DunningManagementSagaCompleted;
use App\Domain\Payment\Events\DunningManagementSagaFailed;
use App\Domain\Payment\Events\DunningManagementSagaStarted;
use App\Domain\Payment\Events\DunningManagementSagaStateChanged;
use App\Domain\Payment\Events\DunningRetryScheduled;
use PHPUnit\Framework\TestCase;

class DunningManagementSagaTest extends TestCase
{
    private string $sagaUuid = '550e8400-e29b-41d4-a716-446655440003';

    public function test_start_dunning_management_saga(): void
    {
        $payload = [
            'subscription_id' => 1,
            'user_id' => 1,
            'amount' => 99.99,
            'max_attempts' => 5,
            'retry_schedule' => [1, 3, 7, 14, 30],
        ];

        $saga = DunningManagementSaga::start($this->sagaUuid, $payload);

        $this->assertEquals($this->sagaUuid, $saga->uuid);
        $this->assertEquals('pending_retry', $saga->state);
        $this->assertEquals(1, $saga->subscriptionId);
        $this->assertEquals(1, $saga->userId);
        $this->assertEquals(99.99, $saga->amount);
        $this->assertEquals(5, $saga->maxAttempts);
        $this->assertCount(1, $saga->getRecordedEvents());
        $this->assertInstanceOf(DunningManagementSagaStarted::class, $saga->getRecordedEvents()[0]);
    }

    public function test_initiate_dunning(): void
    {
        $payload = ['subscription_id' => 1, 'user_id' => 1, 'amount' => 99.99];
        $saga = DunningManagementSaga::start($this->sagaUuid, $payload);

        $dunningPayload = [
            'payment_method_id' => 1,
            'reason' => 'Payment failed',
            'initiated_at' => now()->toDateTimeString(),
        ];
        $saga->initiateDunning($dunningPayload);

        $this->assertCount(2, $saga->getRecordedEvents());
        $this->assertInstanceOf(DunningInitiated::class, $saga->getRecordedEvents()[1]);
    }

    public function test_schedule_retry(): void
    {
        $payload = ['subscription_id' => 1, 'user_id' => 1, 'amount' => 99.99];
        $saga = DunningManagementSaga::start($this->sagaUuid, $payload);

        $retryPayload = [
            'subscription_id' => 1,
            'user_id' => 1,
            'attempt_number' => 1,
            'scheduled_for' => now()->addDay()->toDateTimeString(),
            'days_until_retry' => 1,
            'notification_channels' => ['email', 'sms'],
        ];
        $saga->scheduleRetry($retryPayload);

        $this->assertEquals('pending_payment_attempt', $saga->state);
        $this->assertEquals(1, $saga->attemptNumber);
        $this->assertCount(2, $saga->getRecordedEvents());
        $this->assertInstanceOf(DunningRetryScheduled::class, $saga->getRecordedEvents()[1]);
    }

    public function test_trigger_escalation(): void
    {
        $payload = ['subscription_id' => 1, 'user_id' => 1, 'amount' => 99.99];
        $saga = DunningManagementSaga::start($this->sagaUuid, $payload);

        $escalationPayload = [
            'subscription_id' => 1,
            'user_id' => 1,
            'attempt_number' => 3,
            'escalation_level' => 2,
            'actions' => ['email', 'sms', 'phone', 'pause_service'],
            'triggered_at' => now()->toDateTimeString(),
        ];
        $saga->triggerEscalation($escalationPayload);

        $this->assertEquals('escalated', $saga->state);
        $this->assertCount(2, $saga->getRecordedEvents());
        $this->assertInstanceOf(DunningEscalationTriggered::class, $saga->getRecordedEvents()[1]);
    }

    public function test_transition_to_next_state(): void
    {
        $payload = ['subscription_id' => 1, 'user_id' => 1, 'amount' => 99.99];
        $saga = DunningManagementSaga::start($this->sagaUuid, $payload);

        $saga->transitionTo('pending_payment_attempt', 'retry_scheduled');

        $this->assertEquals('pending_payment_attempt', $saga->state);
        $this->assertCount(2, $saga->getRecordedEvents());
        $this->assertInstanceOf(DunningManagementSagaStateChanged::class, $saga->getRecordedEvents()[1]);
    }

    public function test_complete_saga(): void
    {
        $payload = ['subscription_id' => 1, 'user_id' => 1, 'amount' => 99.99];
        $saga = DunningManagementSaga::start($this->sagaUuid, $payload);

        $completionPayload = [
            'transaction_id' => 'txn_123',
            'attempt_number' => 2,
            'completed_at' => now()->toDateTimeString(),
        ];
        $saga->complete($completionPayload);

        $this->assertEquals('completed', $saga->state);
        $this->assertCount(2, $saga->getRecordedEvents());
        $this->assertInstanceOf(DunningManagementSagaCompleted::class, $saga->getRecordedEvents()[1]);
    }

    public function test_fail_saga(): void
    {
        $payload = ['subscription_id' => 1, 'user_id' => 1, 'amount' => 99.99];
        $saga = DunningManagementSaga::start($this->sagaUuid, $payload);

        $failurePayload = [
            'attempts_made' => 5,
            'reason' => 'All retry attempts exhausted',
            'failed_at' => now()->toDateTimeString(),
        ];
        $saga->fail($failurePayload);

        $this->assertEquals('failed', $saga->state);
        $this->assertCount(2, $saga->getRecordedEvents());
        $this->assertInstanceOf(DunningManagementSagaFailed::class, $saga->getRecordedEvents()[1]);
    }

    public function test_reconstitute_from_history(): void
    {
        $events = [
            new DunningManagementSagaStarted($this->sagaUuid, [
                'subscription_id' => 1,
                'user_id' => 1,
                'amount' => 99.99,
                'max_attempts' => 5,
                'retry_schedule' => [1, 3, 7, 14, 30],
            ]),
            new DunningRetryScheduled($this->sagaUuid, [
                'subscription_id' => 1,
                'user_id' => 1,
                'attempt_number' => 1,
                'days_until_retry' => 1,
                'notification_channels' => ['email'],
            ]),
            new DunningEscalationTriggered($this->sagaUuid, [
                'subscription_id' => 1,
                'user_id' => 1,
                'attempt_number' => 1,
                'escalation_level' => 1,
                'actions' => ['email', 'sms'],
            ]),
            new DunningManagementSagaCompleted($this->sagaUuid, ['transaction_id' => 'txn_123']),
        ];

        $saga = DunningManagementSaga::reconstituteFromHistory($events);

        $this->assertEquals($this->sagaUuid, $saga->uuid);
        $this->assertEquals('completed', $saga->state);
        $this->assertEquals(1, $saga->attemptNumber);
    }

    public function test_event_types(): void
    {
        $event = new DunningManagementSagaStarted($this->sagaUuid);
        $this->assertEquals('dunning_management_saga', $event->aggregateType());
        $this->assertEquals('dunning_management_saga.started', $event->eventType());
    }

    public function test_valid_state_transitions(): void
    {
        $payload = ['subscription_id' => 1, 'user_id' => 1, 'amount' => 99.99];
        $saga = DunningManagementSaga::start($this->sagaUuid, $payload);

        // Valid: pending_retry -> pending_payment_attempt
        $saga->transitionTo('pending_payment_attempt', 'retry_scheduled');
        $this->assertEquals('pending_payment_attempt', $saga->state);

        // Valid: pending_payment_attempt -> escalated
        $saga->transitionTo('escalated', 'payment_failed');
        $this->assertEquals('escalated', $saga->state);

        // Valid: escalated -> completed
        $saga->transitionTo('completed', 'payment_succeeded');
        $this->assertEquals('completed', $saga->state);
    }

    public function test_invalid_state_transition_throws_exception(): void
    {
        $payload = ['subscription_id' => 1, 'user_id' => 1, 'amount' => 99.99];
        $saga = DunningManagementSaga::start($this->sagaUuid, $payload);

        // Invalid: pending_retry -> completed (must go through pending_payment_attempt)
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid transition from 'pending_retry' to 'completed'");

        $saga->transitionTo('completed', 'invalid_transition');
    }

    public function test_terminal_state_cannot_transition(): void
    {
        $payload = ['subscription_id' => 1, 'user_id' => 1, 'amount' => 99.99];
        $saga = DunningManagementSaga::start($this->sagaUuid, $payload);

        $saga->complete(['transaction_id' => 'txn_123']);
        $this->assertEquals('completed', $saga->state);

        // Invalid: completed is terminal state
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid transition from 'completed' to 'failed'");

        $saga->transitionTo('failed', 'invalid_transition');
    }
}

