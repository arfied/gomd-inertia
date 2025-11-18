# Order Fulfillment Saga - Testing Guide

## Unit Tests

### Test Saga State Transitions

```php
namespace Tests\Unit\Domain\Order;

use App\Domain\Order\OrderFulfillmentSaga;
use App\Domain\Order\Events\OrderFulfillmentSagaStarted;
use App\Domain\Order\Events\OrderFulfillmentSagaStateChanged;
use PHPUnit\Framework\TestCase;

class OrderFulfillmentSagaTest extends TestCase
{
    public function test_saga_starts_in_pending_prescription_state(): void
    {
        $saga = OrderFulfillmentSaga::start(
            'saga-1',
            'order-1',
            ['source' => 'test']
        );

        $this->assertEquals('PENDING_PRESCRIPTION', $saga->state);
        $this->assertEquals('saga-1', $saga->uuid);
        $this->assertEquals('order-1', $saga->orderId);
    }

    public function test_saga_transitions_to_pending_inventory_reservation(): void
    {
        $saga = OrderFulfillmentSaga::start('saga-1', 'order-1');
        $saga->transitionTo(
            'PENDING_INVENTORY_RESERVATION',
            'prescription_created',
            ['prescription_id' => 'rx-1']
        );

        $this->assertEquals('PENDING_INVENTORY_RESERVATION', $saga->state);
    }

    public function test_saga_records_compensation_actions(): void
    {
        $saga = OrderFulfillmentSaga::start('saga-1', 'order-1');
        $saga->recordCompensation('cancel_prescription', ['prescription_id' => 'rx-1']);

        $this->assertCount(1, $saga->compensationStack);
        $this->assertEquals('cancel_prescription', $saga->compensationStack[0]['action']);
    }

    public function test_saga_fails_with_compensation_stack(): void
    {
        $saga = OrderFulfillmentSaga::start('saga-1', 'order-1');
        $saga->recordCompensation('cancel_prescription', ['prescription_id' => 'rx-1']);
        $saga->fail('Inventory unavailable', 'inventory_reservation');

        $this->assertEquals('FAILED', $saga->state);
        $this->assertCount(1, $saga->compensationStack);
    }

    public function test_saga_completes_successfully(): void
    {
        $saga = OrderFulfillmentSaga::start('saga-1', 'order-1');
        $saga->complete();

        $this->assertEquals('COMPLETED', $saga->state);
        $this->assertNotNull($saga->completedAt);
    }
}
```

## Feature Tests

### Test Happy Path

```php
namespace Tests\Feature\Order;

use App\Domain\Order\Events\OrderCreated;
use App\Domain\Order\Events\PrescriptionCreated;
use App\Domain\Order\Events\InventoryReserved;
use App\Domain\Order\Events\ShipmentInitiated;
use App\Jobs\Order\CreatePrescriptionJob;
use App\Jobs\Order\ReserveInventoryJob;
use App\Jobs\Order\InitiateShipmentJob;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OrderFulfillmentSagaHappyPathTest extends TestCase
{
    public function test_saga_completes_successfully(): void
    {
        Queue::fake();
        Event::fake();

        // Step 1: Create order
        $event = new OrderCreated(
            'order-1',
            ['patient_id' => 'patient-1', 'medications' => ['med-1']],
            []
        );

        event($event);

        // Assert CreatePrescriptionJob was dispatched
        Queue::assertPushed(CreatePrescriptionJob::class);

        // Step 2: Simulate prescription creation
        $prescriptionEvent = new PrescriptionCreated(
            'order-1',
            ['prescription_id' => 'rx-1', 'medications' => ['med-1']],
            []
        );

        event($prescriptionEvent);

        // Assert ReserveInventoryJob was dispatched
        Queue::assertPushed(ReserveInventoryJob::class);

        // Step 3: Simulate inventory reservation
        $inventoryEvent = new InventoryReserved(
            'order-1',
            ['reservation_id' => 'inv-1', 'medications' => ['med-1']],
            []
        );

        event($inventoryEvent);

        // Assert InitiateShipmentJob was dispatched
        Queue::assertPushed(InitiateShipmentJob::class);

        // Step 4: Simulate shipment initiation
        $shipmentEvent = new ShipmentInitiated(
            'order-1',
            ['shipment_id' => 'ship-1', 'initiated_at' => now()->toIso8601String()],
            []
        );

        event($shipmentEvent);

        // Saga should be complete
        Event::assertDispatched(ShipmentInitiated::class);
    }
}
```

### Test Failure Path - Prescription Fails

```php
namespace Tests\Feature\Order;

use App\Domain\Order\Events\OrderCreated;
use App\Domain\Order\Events\PrescriptionFailed;
use App\Jobs\Order\CreatePrescriptionJob;
use App\Jobs\Order\CancelOrderJob;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OrderFulfillmentSagaFailurePathTest extends TestCase
{
    public function test_saga_compensates_when_prescription_fails(): void
    {
        Queue::fake();
        Event::fake();

        // Step 1: Create order
        $event = new OrderCreated(
            'order-1',
            ['patient_id' => 'patient-1', 'medications' => ['med-1']],
            []
        );

        event($event);

        Queue::assertPushed(CreatePrescriptionJob::class);

        // Step 2: Prescription fails
        $failureEvent = new PrescriptionFailed(
            'order-1',
            ['error' => 'Prescription service unavailable'],
            []
        );

        event($failureEvent);

        // Assert CancelOrderJob was dispatched for compensation
        Queue::assertPushed(CancelOrderJob::class);
    }
}
```

### Test Failure Path - Inventory Fails

```php
public function test_saga_compensates_when_inventory_fails(): void
{
    Queue::fake();
    Event::fake();

    // Create order and prescription
    event(new OrderCreated('order-1', ['patient_id' => 'p-1'], []));
    event(new PrescriptionCreated('order-1', ['prescription_id' => 'rx-1'], []));

    // Inventory fails
    event(new InventoryReservationFailed(
        'order-1',
        ['error' => 'Out of stock'],
        []
    ));

    // Assert compensation chain: CancelPrescription → CancelOrder
    Queue::assertPushed(CancelPrescriptionJob::class);
}
```

### Test Failure Path - Shipment Fails

```php
public function test_saga_compensates_when_shipment_fails(): void
{
    Queue::fake();
    Event::fake();

    // Create order, prescription, and reserve inventory
    event(new OrderCreated('order-1', ['patient_id' => 'p-1'], []));
    event(new PrescriptionCreated('order-1', ['prescription_id' => 'rx-1'], []));
    event(new InventoryReserved('order-1', ['reservation_id' => 'inv-1'], []));

    // Shipment fails
    event(new ShipmentInitiationFailed(
        'order-1',
        ['error' => 'Shipping provider error'],
        []
    ));

    // Assert compensation chain: ReleaseInventory → CancelPrescription → CancelOrder
    Queue::assertPushed(ReleaseInventoryJob::class);
}
```

## Integration Tests

### Test Event Store Persistence

```php
public function test_saga_events_persisted_to_event_store(): void
{
    $saga = OrderFulfillmentSaga::start('saga-1', 'order-1');
    $saga->transitionTo('PENDING_INVENTORY_RESERVATION', 'prescription_created');

    $events = $saga->releaseEvents();

    foreach ($events as $event) {
        $event->store();
    }

    $this->assertDatabaseHas('event_store', [
        'aggregate_uuid' => 'saga-1',
        'aggregate_type' => 'order_fulfillment_saga',
        'event_type' => 'order_fulfillment_saga.state_changed',
    ]);
}
```

## Running Tests

```bash
# Run all saga tests
php artisan test tests/Unit/Domain/Order/OrderFulfillmentSagaTest.php
php artisan test tests/Feature/Order/OrderFulfillmentSagaHappyPathTest.php
php artisan test tests/Feature/Order/OrderFulfillmentSagaFailurePathTest.php

# Run with coverage
php artisan test --coverage tests/Unit/Domain/Order/

# Run specific test
php artisan test tests/Feature/Order/OrderFulfillmentSagaHappyPathTest.php::test_saga_completes_successfully
```
