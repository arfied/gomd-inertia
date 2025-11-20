<?php

namespace Tests\Unit\Domain\Payment;

use App\Domain\Payment\Events\PaymentMethodAdded;
use App\Domain\Payment\Events\PaymentMethodRemoved;
use App\Domain\Payment\Events\PaymentMethodSetAsDefault;
use App\Domain\Payment\Events\PaymentMethodUpdated;
use App\Domain\Payment\PaymentMethodAggregate;
use PHPUnit\Framework\TestCase;

class PaymentMethodAggregateTest extends TestCase
{
    private string $paymentMethodUuid = '550e8400-e29b-41d4-a716-446655440001';

    public function test_add_payment_method_records_payment_method_added_event(): void
    {
        $payload = [
            'user_id' => 1,
            'type' => 'credit_card',
            'is_default' => true,
            'cc_last_four' => '4242',
            'cc_brand' => 'Visa',
            'cc_expiration_month' => '12',
            'cc_expiration_year' => '2025',
            'cc_token' => 'token_123',
        ];

        $aggregate = PaymentMethodAggregate::add($this->paymentMethodUuid, $payload);

        $this->assertEquals($this->paymentMethodUuid, $aggregate->uuid);
        $this->assertEquals('credit_card', $aggregate->type);
        $this->assertTrue($aggregate->isDefault);
        $this->assertFalse($aggregate->isRemoved);
        $this->assertCount(1, $aggregate->getRecordedEvents());
        $this->assertInstanceOf(PaymentMethodAdded::class, $aggregate->getRecordedEvents()[0]);
    }

    public function test_add_ach_payment_method(): void
    {
        $payload = [
            'user_id' => 1,
            'type' => 'ach',
            'is_default' => false,
            'ach_account_name' => 'John Doe',
            'ach_account_type' => 'checking',
            'ach_routing_number_last_four' => '0001',
            'ach_account_number_last_four' => '1234',
            'ach_token' => 'ach_token_123',
        ];

        $aggregate = PaymentMethodAggregate::add($this->paymentMethodUuid, $payload);

        $this->assertEquals('ach', $aggregate->type);
        $this->assertFalse($aggregate->isDefault);
    }

    public function test_update_payment_method_records_payment_method_updated_event(): void
    {
        $payload = [
            'updated_fields' => ['cc_expiration_month', 'cc_expiration_year'],
            'previous_values' => ['cc_expiration_month' => '12', 'cc_expiration_year' => '2024'],
            'new_values' => ['cc_expiration_month' => '12', 'cc_expiration_year' => '2025'],
        ];

        $aggregate = PaymentMethodAggregate::update($this->paymentMethodUuid, $payload);

        $this->assertEquals($this->paymentMethodUuid, $aggregate->uuid);
        $this->assertCount(1, $aggregate->getRecordedEvents());
        $this->assertInstanceOf(PaymentMethodUpdated::class, $aggregate->getRecordedEvents()[0]);
    }

    public function test_remove_payment_method_records_payment_method_removed_event(): void
    {
        $payload = [
            'reason' => 'user_requested',
            'removed_at' => now()->toDateTimeString(),
        ];

        $aggregate = PaymentMethodAggregate::remove($this->paymentMethodUuid, $payload);

        $this->assertEquals($this->paymentMethodUuid, $aggregate->uuid);
        $this->assertTrue($aggregate->isRemoved);
        $this->assertCount(1, $aggregate->getRecordedEvents());
        $this->assertInstanceOf(PaymentMethodRemoved::class, $aggregate->getRecordedEvents()[0]);
    }

    public function test_set_as_default_records_payment_method_set_as_default_event(): void
    {
        $payload = [
            'previous_default_id' => 2,
            'set_as_default_at' => now()->toDateTimeString(),
        ];

        $aggregate = PaymentMethodAggregate::setAsDefault($this->paymentMethodUuid, $payload);

        $this->assertEquals($this->paymentMethodUuid, $aggregate->uuid);
        $this->assertTrue($aggregate->isDefault);
        $this->assertCount(1, $aggregate->getRecordedEvents());
        $this->assertInstanceOf(PaymentMethodSetAsDefault::class, $aggregate->getRecordedEvents()[0]);
    }

    public function test_reconstitute_from_history_applies_all_events(): void
    {
        $addPayload = [
            'user_id' => 1,
            'type' => 'credit_card',
            'is_default' => false,
            'cc_last_four' => '4242',
            'cc_brand' => 'Visa',
        ];

        $setDefaultPayload = [
            'previous_default_id' => null,
            'set_as_default_at' => now()->toDateTimeString(),
        ];

        $events = [
            new PaymentMethodAdded($this->paymentMethodUuid, $addPayload),
            new PaymentMethodSetAsDefault($this->paymentMethodUuid, $setDefaultPayload),
        ];

        $aggregate = PaymentMethodAggregate::reconstituteFromHistory($events);

        $this->assertEquals($this->paymentMethodUuid, $aggregate->uuid);
        $this->assertEquals('credit_card', $aggregate->type);
        $this->assertTrue($aggregate->isDefault);
        $this->assertFalse($aggregate->isRemoved);
    }

    public function test_payment_method_added_event_has_correct_aggregate_type(): void
    {
        $event = new PaymentMethodAdded($this->paymentMethodUuid);

        $this->assertEquals('payment_method', $event->aggregateType());
        $this->assertEquals('payment_method.added', $event->eventType());
    }

    public function test_payment_method_updated_event_has_correct_aggregate_type(): void
    {
        $event = new PaymentMethodUpdated($this->paymentMethodUuid);

        $this->assertEquals('payment_method', $event->aggregateType());
        $this->assertEquals('payment_method.updated', $event->eventType());
    }

    public function test_payment_method_removed_event_has_correct_aggregate_type(): void
    {
        $event = new PaymentMethodRemoved($this->paymentMethodUuid);

        $this->assertEquals('payment_method', $event->aggregateType());
        $this->assertEquals('payment_method.removed', $event->eventType());
    }

    public function test_payment_method_set_as_default_event_has_correct_aggregate_type(): void
    {
        $event = new PaymentMethodSetAsDefault($this->paymentMethodUuid);

        $this->assertEquals('payment_method', $event->aggregateType());
        $this->assertEquals('payment_method.set_as_default', $event->eventType());
    }
}

