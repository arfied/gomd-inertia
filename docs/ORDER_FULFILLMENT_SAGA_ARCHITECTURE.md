# Order Fulfillment Saga Architecture for Laravel 12

## Overview

This document describes a distributed saga pattern implementation for order fulfillment using Laravel 12's event sourcing, CQRS, and queue system. The saga orchestrates a multi-step process with compensation actions for failure scenarios.

## Saga Flow

```
┌─────────────────────────────────────────────────────────────────────┐
│                    ORDER FULFILLMENT SAGA                           │
└─────────────────────────────────────────────────────────────────────┘

1. ORDER CREATION
   ├─ Command: CreateOrder
   ├─ Event: OrderCreated
   └─ State: PENDING_PRESCRIPTION

2. PRESCRIPTION CREATION
   ├─ Command: CreatePrescription
   ├─ Event: PrescriptionCreated
   └─ State: PENDING_INVENTORY_RESERVATION

3. INVENTORY RESERVATION
   ├─ Command: ReserveInventory
   ├─ Event: InventoryReserved
   └─ State: PENDING_SHIPMENT

4. SHIPMENT INITIATION
   ├─ Command: InitiateShipment
   ├─ Event: ShipmentInitiated
   └─ State: COMPLETED

FAILURE PATHS (Compensation):
   ├─ Shipment Failed → Release Inventory → Cancel Prescription → Cancel Order
   ├─ Inventory Failed → Cancel Prescription → Cancel Order
   └─ Prescription Failed → Cancel Order
```

## State Machine

```
PENDING_PRESCRIPTION
    ↓ (PrescriptionCreated)
PENDING_INVENTORY_RESERVATION
    ↓ (InventoryReserved)
PENDING_SHIPMENT
    ↓ (ShipmentInitiated)
COMPLETED

FAILURE STATES:
    ↓ (PrescriptionFailed)
CANCELLED_PRESCRIPTION_FAILED
    ↓ (OrderCancelled)
CANCELLED

    ↓ (InventoryReservationFailed)
CANCELLED_INVENTORY_FAILED
    ↓ (PrescriptionCancelled)
    ↓ (OrderCancelled)
CANCELLED

    ↓ (ShipmentInitiationFailed)
CANCELLED_SHIPMENT_FAILED
    ↓ (InventoryReleased)
    ↓ (PrescriptionCancelled)
    ↓ (OrderCancelled)
CANCELLED
```

## Saga Orchestration Pattern

The saga uses **choreography** (event-driven) combined with **orchestration** (saga coordinator):

- **Choreography**: Services publish domain events
- **Orchestration**: `OrderFulfillmentSaga` coordinates the flow and handles compensation

## Core Components

### 1. Saga State Aggregate
- Tracks saga instance state
- Stores compensation metadata
- Persists to `order_fulfillment_sagas` table

### 2. Domain Events
- `OrderCreated`
- `PrescriptionCreated` / `PrescriptionFailed`
- `InventoryReserved` / `InventoryReservationFailed`
- `ShipmentInitiated` / `ShipmentInitiationFailed`
- Compensation events: `OrderCancelled`, `PrescriptionCancelled`, `InventoryReleased`

### 3. Saga Coordinator
- Listens to domain events
- Dispatches next commands
- Triggers compensation on failures

### 4. Queue Jobs
- `CreatePrescriptionJob`
- `ReserveInventoryJob`
- `InitiateShipmentJob`
- Compensation jobs for rollback

## Compensation Actions

| Step | Failure | Compensation |
|------|---------|--------------|
| Prescription | PrescriptionFailed | CancelOrder |
| Inventory | InventoryReservationFailed | CancelPrescription → CancelOrder |
| Shipment | ShipmentInitiationFailed | ReleaseInventory → CancelPrescription → CancelOrder |

## Implementation Details

See implementation files:
- `app/Domain/Order/OrderFulfillmentSaga.php`
- `app/Domain/Order/Events/OrderFulfillmentSagaStarted.php`
- `app/Application/Order/Handlers/OrderFulfillmentSagaHandler.php`
- `app/Jobs/Order/CreatePrescriptionJob.php`
- `app/Jobs/Order/ReserveInventoryJob.php`
- `app/Jobs/Order/InitiateShipmentJob.php`
- `database/migrations/2025_11_18_000000_create_order_fulfillment_sagas_table.php`

## Idempotency & Deduplication

- Each saga instance has unique `saga_uuid`
- Commands include `idempotency_key`
- Event store prevents duplicate events
- Queue jobs use `onQueue()` with unique identifiers

## Monitoring & Observability

- Saga state transitions logged
- Compensation actions tracked
- Failed sagas stored for manual intervention
- Metrics: saga success rate, average duration, compensation frequency

---

## Detailed Implementation Guide

### Step 1: Register Events in Config

Update `config/projection_replay.php` to include saga events:

```php
'event_types' => [
    // ... existing events ...
    'order.prescription_created' => App\Domain\Order\Events\PrescriptionCreated::class,
    'order.prescription_failed' => App\Domain\Order\Events\PrescriptionFailed::class,
    'order.inventory_reserved' => App\Domain\Order\Events\InventoryReserved::class,
    'order.inventory_reservation_failed' => App\Domain\Order\Events\InventoryReservationFailed::class,
    'order.shipment_initiated' => App\Domain\Order\Events\ShipmentInitiated::class,
    'order.shipment_initiation_failed' => App\Domain\Order\Events\ShipmentInitiationFailed::class,
    'order.prescription_cancelled' => App\Domain\Order\Events\PrescriptionCancelled::class,
    'order.inventory_released' => App\Domain\Order\Events\InventoryReleased::class,
    'order_fulfillment_saga.started' => App\Domain\Order\Events\OrderFulfillmentSagaStarted::class,
    'order_fulfillment_saga.state_changed' => App\Domain\Order\Events\OrderFulfillmentSagaStateChanged::class,
    'order_fulfillment_saga.compensation_recorded' => App\Domain\Order\Events\CompensationRecorded::class,
    'order_fulfillment_saga.failed' => App\Domain\Order\Events\OrderFulfillmentSagaFailed::class,
    'order_fulfillment_saga.completed' => App\Domain\Order\Events\OrderFulfillmentSagaCompleted::class,
];
```

### Step 2: Register Event Listeners

In `app/Providers/EventServiceProvider.php`:

```php
protected $listen = [
    OrderCreated::class => [
        OrderFulfillmentSagaHandler::class,
    ],
    PrescriptionCreated::class => [
        OrderFulfillmentSagaHandler::class,
    ],
    PrescriptionFailed::class => [
        OrderFulfillmentSagaHandler::class,
    ],
    InventoryReserved::class => [
        OrderFulfillmentSagaHandler::class,
    ],
    InventoryReservationFailed::class => [
        OrderFulfillmentSagaHandler::class,
    ],
    ShipmentInitiated::class => [
        OrderFulfillmentSagaHandler::class,
    ],
    ShipmentInitiationFailed::class => [
        OrderFulfillmentSagaHandler::class,
    ],
];
```

### Step 3: Create Order Command

```php
namespace App\Application\Order\Commands;

class CreateOrder
{
    public function __construct(
        public string $orderId,
        public string $patientId,
        public array $medications,
        public array $metadata = [],
    ) {
    }
}
```

### Step 4: Create Order Handler

```php
namespace App\Application\Order\Handlers;

use App\Domain\Order\Events\OrderCreated;
use App\Services\EventStore;

class CreateOrderHandler
{
    public function __construct(private EventStore $eventStore) {}

    public function handle(CreateOrder $command): void
    {
        $event = new OrderCreated(
            $command->orderId,
            [
                'patient_id' => $command->patientId,
                'medications' => $command->medications,
            ],
            $command->metadata
        );

        $this->eventStore->store($event);
        event($event);
    }
}
```

### Step 5: Queue Configuration

Ensure `config/queue.php` has the `order-fulfillment` queue configured:

```php
'connections' => [
    'database' => [
        'driver' => 'database',
        'connection' => env('DB_QUEUE_CONNECTION'),
        'table' => env('DB_QUEUE_TABLE', 'jobs'),
        'queue' => env('DB_QUEUE', 'default'),
        'retry_after' => 90,
        'after_commit' => false,
    ],
],
```

### Step 6: Run Queue Worker

```bash
php artisan queue:work --queue=order-fulfillment --tries=3
```

### Step 7: Monitor Saga Progress

Query saga state:

```php
use App\Models\OrderFulfillmentSaga;

// Get pending sagas
$pending = OrderFulfillmentSaga::pending()->get();

// Get failed sagas
$failed = OrderFulfillmentSaga::failed()->get();

// Get saga by order
$saga = OrderFulfillmentSaga::where('order_uuid', $orderId)->first();

// Check duration
$duration = $saga->getDurationSeconds();
```

