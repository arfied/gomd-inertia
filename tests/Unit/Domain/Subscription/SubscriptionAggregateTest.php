<?php

namespace Tests\Unit\Domain\Subscription;

use App\Domain\Subscription\Events\SubscriptionCancelled;
use App\Domain\Subscription\Events\SubscriptionCreated;
use App\Domain\Subscription\Events\SubscriptionExpired;
use App\Domain\Subscription\Events\SubscriptionRenewed;
use App\Domain\Subscription\SubscriptionAggregate;
use PHPUnit\Framework\TestCase;

class SubscriptionAggregateTest extends TestCase
{
    private string $subscriptionUuid = '550e8400-e29b-41d4-a716-446655440000';

    public function test_create_subscription_records_subscription_created_event(): void
    {
        $payload = [
            'user_id' => 1,
            'plan_id' => 1,
            'starts_at' => '2025-01-01 00:00:00',
            'ends_at' => '2025-02-01 00:00:00',
            'status' => 'active',
        ];

        $aggregate = SubscriptionAggregate::create($this->subscriptionUuid, $payload);

        $this->assertEquals($this->subscriptionUuid, $aggregate->uuid);
        $this->assertEquals('active', $aggregate->status);
        $this->assertCount(1, $aggregate->getRecordedEvents());
        $this->assertInstanceOf(SubscriptionCreated::class, $aggregate->getRecordedEvents()[0]);
    }

    public function test_renew_subscription_records_subscription_renewed_event(): void
    {
        $payload = [
            'previous_ends_at' => '2025-02-01 00:00:00',
            'new_ends_at' => '2025-03-01 00:00:00',
            'renewal_reason' => 'automatic',
        ];

        $aggregate = SubscriptionAggregate::renew($this->subscriptionUuid, $payload);

        $this->assertEquals($this->subscriptionUuid, $aggregate->uuid);
        $this->assertEquals('active', $aggregate->status);
        $this->assertCount(1, $aggregate->getRecordedEvents());
        $this->assertInstanceOf(SubscriptionRenewed::class, $aggregate->getRecordedEvents()[0]);
    }

    public function test_cancel_subscription_records_subscription_cancelled_event(): void
    {
        $payload = [
            'cancelled_at' => now()->toDateTimeString(),
            'cancellation_reason' => 'user_requested',
        ];

        $aggregate = SubscriptionAggregate::cancel($this->subscriptionUuid, $payload);

        $this->assertEquals($this->subscriptionUuid, $aggregate->uuid);
        $this->assertEquals('cancelled', $aggregate->status);
        $this->assertCount(1, $aggregate->getRecordedEvents());
        $this->assertInstanceOf(SubscriptionCancelled::class, $aggregate->getRecordedEvents()[0]);
    }

    public function test_expire_subscription_records_subscription_expired_event(): void
    {
        $payload = [
            'expired_at' => now()->toDateTimeString(),
            'reason' => 'end_of_period',
        ];

        $aggregate = SubscriptionAggregate::expire($this->subscriptionUuid, $payload);

        $this->assertEquals($this->subscriptionUuid, $aggregate->uuid);
        $this->assertEquals('expired', $aggregate->status);
        $this->assertCount(1, $aggregate->getRecordedEvents());
        $this->assertInstanceOf(SubscriptionExpired::class, $aggregate->getRecordedEvents()[0]);
    }

    public function test_reconstitute_from_history_applies_all_events(): void
    {
        $createPayload = [
            'user_id' => 1,
            'plan_id' => 1,
            'starts_at' => '2025-01-01 00:00:00',
            'ends_at' => '2025-02-01 00:00:00',
        ];

        $renewPayload = [
            'previous_ends_at' => '2025-02-01 00:00:00',
            'new_ends_at' => '2025-03-01 00:00:00',
            'renewal_reason' => 'automatic',
        ];

        $events = [
            new SubscriptionCreated($this->subscriptionUuid, $createPayload),
            new SubscriptionRenewed($this->subscriptionUuid, $renewPayload),
        ];

        $aggregate = SubscriptionAggregate::reconstituteFromHistory($events);

        $this->assertEquals($this->subscriptionUuid, $aggregate->uuid);
        $this->assertEquals('active', $aggregate->status);
    }

    public function test_subscription_created_event_has_correct_aggregate_type(): void
    {
        $event = new SubscriptionCreated($this->subscriptionUuid);

        $this->assertEquals('subscription', $event->aggregateType());
        $this->assertEquals('subscription.created', $event->eventType());
    }

    public function test_subscription_renewed_event_has_correct_aggregate_type(): void
    {
        $event = new SubscriptionRenewed($this->subscriptionUuid);

        $this->assertEquals('subscription', $event->aggregateType());
        $this->assertEquals('subscription.renewed', $event->eventType());
    }

    public function test_subscription_cancelled_event_has_correct_aggregate_type(): void
    {
        $event = new SubscriptionCancelled($this->subscriptionUuid);

        $this->assertEquals('subscription', $event->aggregateType());
        $this->assertEquals('subscription.cancelled', $event->eventType());
    }

    public function test_subscription_expired_event_has_correct_aggregate_type(): void
    {
        $event = new SubscriptionExpired($this->subscriptionUuid);

        $this->assertEquals('subscription', $event->aggregateType());
        $this->assertEquals('subscription.expired', $event->eventType());
    }
}

