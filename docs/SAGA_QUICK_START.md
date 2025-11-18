# Order Fulfillment Saga - Quick Start Guide

## 5-Minute Overview

The Order Fulfillment Saga is a distributed transaction pattern that orchestrates a multi-step order process with automatic compensation on failure.

### The Flow

```
Order Created → Prescription Created → Inventory Reserved → Shipment Initiated → ✅ Complete
                      ↓                      ↓                      ↓
                   Failed?              Failed?                 Failed?
                      ↓                      ↓                      ↓
                  Cancel Order      Cancel Prescription      Release Inventory
                                    + Cancel Order          + Cancel Prescription
                                                            + Cancel Order
```

## Files Created

### Core Saga
- `app/Domain/Order/OrderFulfillmentSaga.php` - Saga state aggregate
- `app/Models/OrderFulfillmentSaga.php` - Eloquent model for saga state

### Domain Events (13 total)
- `app/Domain/Order/Events/OrderFulfillmentSagaStarted.php`
- `app/Domain/Order/Events/OrderFulfillmentSagaStateChanged.php`
- `app/Domain/Order/Events/CompensationRecorded.php`
- `app/Domain/Order/Events/OrderFulfillmentSagaFailed.php`
- `app/Domain/Order/Events/OrderFulfillmentSagaCompleted.php`
- `app/Domain/Order/Events/PrescriptionCreated.php`
- `app/Domain/Order/Events/PrescriptionFailed.php`
- `app/Domain/Order/Events/InventoryReserved.php`
- `app/Domain/Order/Events/InventoryReservationFailed.php`
- `app/Domain/Order/Events/ShipmentInitiated.php`
- `app/Domain/Order/Events/ShipmentInitiationFailed.php`
- `app/Domain/Order/Events/PrescriptionCancelled.php`
- `app/Domain/Order/Events/InventoryReleased.php`

### Queue Jobs (6 total)
- `app/Jobs/Order/CreatePrescriptionJob.php` - Step 1
- `app/Jobs/Order/ReserveInventoryJob.php` - Step 2
- `app/Jobs/Order/InitiateShipmentJob.php` - Step 3
- `app/Jobs/Order/CancelOrderJob.php` - Compensation 1
- `app/Jobs/Order/CancelPrescriptionJob.php` - Compensation 2
- `app/Jobs/Order/ReleaseInventoryJob.php` - Compensation 3

### Orchestration (7 Event Listeners)
- `app/Application/Order/Handlers/OrderFulfillmentSagaHandler.php` - Handles OrderCreated
- `app/Application/Order/Handlers/PrescriptionCreatedHandler.php` - Handles PrescriptionCreated
- `app/Application/Order/Handlers/PrescriptionFailedHandler.php` - Handles PrescriptionFailed
- `app/Application/Order/Handlers/InventoryReservedHandler.php` - Handles InventoryReserved
- `app/Application/Order/Handlers/InventoryReservationFailedHandler.php` - Handles InventoryReservationFailed
- `app/Application/Order/Handlers/ShipmentInitiatedHandler.php` - Handles ShipmentInitiated
- `app/Application/Order/Handlers/ShipmentInitiationFailedHandler.php` - Handles ShipmentInitiationFailed

### Database
- `database/migrations/2025_11_18_000000_create_order_fulfillment_sagas_table.php`

## Setup Steps

### 1. Run Migration
```bash
php artisan migrate
```

### 2. Update Config
Add to `config/projection_replay.php`:
```php
'event_types' => [
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

### 3. Event Listeners (Automatic Discovery)
Laravel 12 automatically discovers event listeners! No manual registration needed.

The `OrderFulfillmentSagaHandler` is already in `app/Application/Order/Handlers/` with a `handle()` method. Laravel will automatically register it.

**Verify it's working:**
```bash
php artisan event:list
```

### 4. Start Queue Worker
```bash
php artisan queue:work --queue=order-fulfillment --tries=3
```

## Usage Example

```php
// Create order (triggers saga)
$event = new OrderCreated(
    'order-123',
    [
        'patient_id' => 'patient-456',
        'medications' => ['med-1', 'med-2'],
    ],
    ['source' => 'api']
);

event($event);

// Saga automatically:
// 1. Creates prescription
// 2. Reserves inventory
// 3. Initiates shipment
// 4. Completes or compensates on failure
```

## Monitoring

```php
// Check saga status
$saga = OrderFulfillmentSaga::where('order_uuid', 'order-123')->first();

echo $saga->state; // PENDING_PRESCRIPTION, PENDING_INVENTORY, etc.
echo $saga->getDurationSeconds(); // How long saga has been running

// Get pending sagas
$pending = OrderFulfillmentSaga::pending()->count();

// Get failed sagas
$failed = OrderFulfillmentSaga::failed()->get();

// Get completed sagas
$completed = OrderFulfillmentSaga::completed()->count();
```

## Documentation

- **Architecture**: `docs/ORDER_FULFILLMENT_SAGA_ARCHITECTURE.md`
- **Flow Diagrams**: `docs/SAGA_FLOW_DIAGRAMS.md`
- **Testing Guide**: `docs/SAGA_TESTING_GUIDE.md`
- **Implementation Checklist**: `docs/SAGA_IMPLEMENTATION_CHECKLIST.md`
- **Advanced Patterns**: `docs/SAGA_ADVANCED_PATTERNS.md`

## Key Concepts

### Event Sourcing
All saga state changes are stored as immutable events in the `event_store` table.

### CQRS
Commands trigger events, which are processed by handlers to update read models.

### Saga Pattern
Distributed transaction with compensation actions for failure scenarios.

### Choreography
Services publish events; other services listen and react.

### Compensation
Automatic rollback of completed steps when a later step fails.

## Troubleshooting

**Saga stuck in pending state?**
- Check queue worker: `ps aux | grep queue:work`
- Check failed jobs: `php artisan queue:failed`
- Check logs: `tail -f storage/logs/laravel.log`

**Compensation not triggered?**
- Verify event listener registered: `php artisan event:list`
- Check handler is public method
- Verify event is dispatched: `Event::fake()` in tests

**Duplicate events?**
- Use idempotency keys
- Check for job retries
- Implement deduplication logic

## Next Steps

1. Implement TODO sections in job files
2. Write tests for your specific business logic
3. Set up monitoring and alerting
4. Deploy to staging environment
5. Load test with realistic order volumes
6. Monitor production for issues

## Support

See `docs/SAGA_ADVANCED_PATTERNS.md` for troubleshooting and advanced patterns.
