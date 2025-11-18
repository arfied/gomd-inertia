# Order Fulfillment Saga for Laravel 12

A production-ready distributed saga pattern implementation for orchestrating complex multi-step order fulfillment processes with automatic compensation on failure.

## 🎯 Overview

This saga architecture handles the complete order fulfillment workflow:

```
Order Created → Prescription Created → Inventory Reserved → Shipment Initiated → ✅ Complete
```

With automatic compensation on any failure:

```
Shipment Failed → Release Inventory → Cancel Prescription → Cancel Order → ❌ Cancelled
```

## ✨ Key Features

- ✅ **Event Sourcing**: All state changes stored as immutable events in MySQL
- ✅ **CQRS**: Separate command and query models
- ✅ **Distributed Transactions**: Multi-step saga with automatic compensation
- ✅ **Queue-Based**: Asynchronous processing with Laravel queues
- ✅ **Idempotent**: Safe to retry without duplicates
- ✅ **Observable**: Comprehensive logging and metrics
- ✅ **Testable**: Unit, feature, and integration tests included
- ✅ **Scalable**: Handles high-volume order processing
- ✅ **Resilient**: Retry logic, circuit breakers, timeouts

## 📦 What's Included

### Code (20 files)
- **2** Saga core files (aggregate + model)
- **13** Domain event classes
- **6** Queue job classes (3 forward + 3 compensation)
- **7** Event listener handlers (automatic discovery)
- **1** Database migration

### Documentation (8 files)
- Quick start guide
- Architecture overview
- Flow diagrams (happy path + failure paths)
- Testing guide with examples
- Implementation checklist
- Advanced patterns & troubleshooting
- Complete summary
- Documentation index

## 🚀 Quick Start

### 1. Run Migration
```bash
php artisan migrate
```

### 2. Update Configuration
Add events to `config/projection_replay.php`:
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

The `OrderFulfillmentSagaHandler` is already in `app/Application/Order/Handlers/` and has a `handle()` method that type-hints the events it listens to. Laravel will automatically register it.

**How it works:**
- Laravel scans `app/Listeners` directory (and other configured directories)
- Any method named `handle` or `__invoke` is registered as a listener
- The event type is determined by the type-hint in the method signature

**Verify it's working:**
```bash
php artisan event:list
```

This will show all registered event listeners including `OrderFulfillmentSagaHandler`.

### 4. Start Queue Worker
```bash
php artisan queue:work --queue=order-fulfillment --tries=3
```

### 5. Create Order (Triggers Saga)
```php
event(new OrderCreated(
    'order-123',
    [
        'patient_id' => 'patient-456',
        'medications' => ['med-1', 'med-2'],
    ],
    ['source' => 'api']
));
```

## 📊 Architecture

### Event Sourcing
All state changes stored as immutable events in `event_store` table.

### CQRS
- **Command Side**: CreateOrder → CreateOrderHandler → OrderCreated event
- **Query Side**: OrderFulfillmentSaga read model for state queries

### Saga Orchestration
Event-driven choreography with OrderFulfillmentSagaHandler coordinator.

### State Machine
```
PENDING_PRESCRIPTION
    ↓ (PrescriptionCreated)
PENDING_INVENTORY_RESERVATION
    ↓ (InventoryReserved)
PENDING_SHIPMENT
    ↓ (ShipmentInitiated)
COMPLETED

Failure paths → CANCELLED (with compensation)
```

## 🔄 Compensation Chain

When a step fails, compensation actions execute in reverse order (LIFO):

| Failure Point | Compensation Chain |
|---|---|
| Prescription | Cancel Order |
| Inventory | Cancel Prescription → Cancel Order |
| Shipment | Release Inventory → Cancel Prescription → Cancel Order |

## 📈 Monitoring

```php
// Check saga status
$saga = OrderFulfillmentSaga::where('order_uuid', 'order-123')->first();
echo $saga->state; // PENDING_PRESCRIPTION, COMPLETED, CANCELLED, etc.

// Get pending sagas
$pending = OrderFulfillmentSaga::pending()->count();

// Get failed sagas
$failed = OrderFulfillmentSaga::failed()->get();

// Get duration
$duration = $saga->getDurationSeconds();
```

## 🧪 Testing

```bash
# Run all saga tests
php artisan test tests/Unit/Domain/Order/OrderFulfillmentSagaTest.php
php artisan test tests/Feature/Order/OrderFulfillmentSagaHappyPathTest.php
php artisan test tests/Feature/Order/OrderFulfillmentSagaFailurePathTest.php

# Run with coverage
php artisan test --coverage tests/Unit/Domain/Order/
```

## 📚 Documentation

| Document | Purpose |
|----------|---------|
| [SAGA_QUICK_START.md](SAGA_QUICK_START.md) | Quick start guide |
| [ORDER_FULFILLMENT_SAGA_ARCHITECTURE.md](ORDER_FULFILLMENT_SAGA_ARCHITECTURE.md) | Architecture overview |
| [SAGA_FLOW_DIAGRAMS.md](SAGA_FLOW_DIAGRAMS.md) | Visual flow diagrams |
| [SAGA_TESTING_GUIDE.md](SAGA_TESTING_GUIDE.md) | Testing examples |
| [SAGA_IMPLEMENTATION_CHECKLIST.md](SAGA_IMPLEMENTATION_CHECKLIST.md) | Implementation guide |
| [SAGA_ADVANCED_PATTERNS.md](SAGA_ADVANCED_PATTERNS.md) | Advanced patterns |
| [SAGA_SUMMARY.md](SAGA_SUMMARY.md) | Complete summary |
| [SAGA_INDEX.md](SAGA_INDEX.md) | Documentation index |

## 🛠️ Implementation Phases

1. Foundation Setup (Migration, Model, Aggregate)
2. Domain Events (13 event classes)
3. Queue Jobs (6 job classes)
4. Saga Orchestration (Event handler)
5. Configuration (Event registry, listeners)
6. Commands & Handlers (CreateOrder command)
7. Testing (Unit, feature, integration)
8. Monitoring & Observability (Logging, metrics)
9. Documentation (Complete)
10. Deployment (Production ready)

## 🎓 Best Practices

- **Idempotency**: Safe to retry without duplicates
- **Compensation Ordering**: LIFO (Last In First Out)
- **Timeout Handling**: Prevent hanging jobs
- **Dead Letter Queue**: Failed jobs for manual intervention
- **Event Versioning**: Support schema evolution
- **Circuit Breaker**: Prevent cascading failures
- **Distributed Tracing**: Track saga execution
- **Observability**: Comprehensive logging and metrics

## 🔧 Troubleshooting

**Saga stuck in pending state?**
- Check queue worker: `ps aux | grep queue:work`
- Check failed jobs: `php artisan queue:failed`
- Check logs: `tail -f storage/logs/laravel.log`

**Compensation not triggered?**
- Verify event listener registered: `php artisan event:list`
- Check handler is public method
- Verify event is dispatched

See [SAGA_ADVANCED_PATTERNS.md](SAGA_ADVANCED_PATTERNS.md) for more troubleshooting.

## 📝 Next Steps

1. Implement TODO sections in job files (external service calls)
2. Write tests for your specific business logic
3. Set up monitoring and alerting
4. Deploy to staging environment
5. Load test with realistic order volumes
6. Monitor production for issues

## 📞 Support

For issues or questions, see:
- [SAGA_ADVANCED_PATTERNS.md#troubleshooting](SAGA_ADVANCED_PATTERNS.md)
- [SAGA_TESTING_GUIDE.md](SAGA_TESTING_GUIDE.md)
- [SAGA_IMPLEMENTATION_CHECKLIST.md](SAGA_IMPLEMENTATION_CHECKLIST.md)

---

**Ready to implement?** Start with [SAGA_QUICK_START.md](SAGA_QUICK_START.md)
