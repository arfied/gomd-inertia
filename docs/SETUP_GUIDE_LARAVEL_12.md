# Order Fulfillment Saga - Setup Guide for Laravel 12

## ✅ What You Need to Know

**Good news!** Laravel 12 has automatic event discovery, so you **don't need to manually register event listeners**.

## 🚀 Quick Setup (3 Steps)

### Step 1: Run Migration
```bash
php artisan migrate
```

This creates the `order_fulfillment_sagas` table.

### Step 2: Update Event Configuration
Add events to `config/projection_replay.php`:

```php
'event_types' => [
    // Saga events
    'order_fulfillment_saga.started' => App\Domain\Order\Events\OrderFulfillmentSagaStarted::class,
    'order_fulfillment_saga.state_changed' => App\Domain\Order\Events\OrderFulfillmentSagaStateChanged::class,
    'order_fulfillment_saga.compensation_recorded' => App\Domain\Order\Events\CompensationRecorded::class,
    'order_fulfillment_saga.failed' => App\Domain\Order\Events\OrderFulfillmentSagaFailed::class,
    'order_fulfillment_saga.completed' => App\Domain\Order\Events\OrderFulfillmentSagaCompleted::class,

    // Order step events
    'order.prescription_created' => App\Domain\Order\Events\PrescriptionCreated::class,
    'order.prescription_failed' => App\Domain\Order\Events\PrescriptionFailed::class,
    'order.inventory_reserved' => App\Domain\Order\Events\InventoryReserved::class,
    'order.inventory_reservation_failed' => App\Domain\Order\Events\InventoryReservationFailed::class,
    'order.shipment_initiated' => App\Domain\Order\Events\ShipmentInitiated::class,
    'order.shipment_initiation_failed' => App\Domain\Order\Events\ShipmentInitiationFailed::class,
    'order.prescription_cancelled' => App\Domain\Order\Events\PrescriptionCancelled::class,
    'order.inventory_released' => App\Domain\Order\Events\InventoryReleased::class,
];
```

### Step 3: Start Queue Worker
```bash
php artisan queue:work --queue=order-fulfillment --tries=3
```

## ✨ That's It!

Event listeners are automatically discovered. No `EventServiceProvider` changes needed!

## 🔍 Verify Setup

### Check Event Listeners
```bash
php artisan event:list
```

You should see:
```
OrderCreated
  App\Application\Order\Handlers\OrderFulfillmentSagaHandler

PrescriptionCreated
  App\Application\Order\Handlers\PrescriptionCreatedHandler

PrescriptionFailed
  App\Application\Order\Handlers\PrescriptionFailedHandler

InventoryReserved
  App\Application\Order\Handlers\InventoryReservedHandler

InventoryReservationFailed
  App\Application\Order\Handlers\InventoryReservationFailedHandler

ShipmentInitiated
  App\Application\Order\Handlers\ShipmentInitiatedHandler

ShipmentInitiationFailed
  App\Application\Order\Handlers\ShipmentInitiationFailedHandler
```

### Test Event Dispatching
```bash
php artisan tinker

>>> event(new \App\Domain\Order\Events\OrderCreated('order-1', ['patient_id' => 'p-1', 'medications' => ['med-1']]));
```

Check queue worker output - you should see jobs being processed.

## 📁 Event Listeners Location

All 7 event listeners are in `app/Listeners/`:
```
app/Listeners/
├── OrderFulfillmentSagaOrderCreatedListener.php
├── OrderFulfillmentSagaPrescriptionCreatedListener.php
├── OrderFulfillmentSagaPrescriptionFailedListener.php
├── OrderFulfillmentSagaInventoryReservedListener.php
├── OrderFulfillmentSagaInventoryReservationFailedListener.php
├── OrderFulfillmentSagaShipmentInitiatedListener.php
└── OrderFulfillmentSagaShipmentInitiationFailedListener.php
```

**Important**: Each listener is in its **own file**. Laravel only discovers one class per file!

Each has a `handle()` method that type-hints its event. Laravel automatically discovers them!

## 🎯 How Automatic Discovery Works

```php
// This is automatically registered for OrderCreated events
class OrderFulfillmentSagaHandler implements ShouldQueue
{
    use Queueable;

    public function handle(OrderCreated $event): void
    {
        // Laravel sees the type-hint and registers this automatically
        dispatch(new CreatePrescriptionJob(...))->onQueue('order-fulfillment');
    }
}
```

**No manual registration needed!**

## 🚨 Troubleshooting

### Listeners Not Showing in `event:list`?

1. Clear cache: `php artisan cache:clear`
2. Verify method is named `handle` or `__invoke`
3. Verify event is type-hinted in method signature
4. Check `config/event.php` for correct paths

### Queue Jobs Not Processing?

1. Check queue worker: `ps aux | grep queue:work`
2. Check failed jobs: `php artisan queue:failed`
3. Check logs: `tail -f storage/logs/laravel.log`

### Event Not Dispatching?

1. Verify event class exists
2. Verify event is in `config/projection_replay.php`
3. Test with `php artisan tinker`

## 📚 Documentation

- **Quick Start**: [SAGA_QUICK_START.md](SAGA_QUICK_START.md)
- **Event Discovery Details**: [LARAVEL_12_EVENT_DISCOVERY.md](LARAVEL_12_EVENT_DISCOVERY.md)
- **Full Architecture**: [ORDER_FULFILLMENT_SAGA_ARCHITECTURE.md](ORDER_FULFILLMENT_SAGA_ARCHITECTURE.md)
- **Implementation Checklist**: [SAGA_IMPLEMENTATION_CHECKLIST.md](SAGA_IMPLEMENTATION_CHECKLIST.md)

## ✅ Next Steps

1. ✅ Run migration
2. ✅ Update `config/projection_replay.php`
3. ✅ Start queue worker
4. ✅ Test with `php artisan tinker`
5. Implement TODO sections in job files
6. Write tests for your business logic
7. Deploy to staging

## 🎓 Key Takeaway

**Laravel 12 automatically discovers event listeners!**

- No manual registration in `EventServiceProvider`
- Just create a handler with a `handle()` method
- Type-hint the event in the method signature
- Laravel does the rest automatically

See [LARAVEL_12_EVENT_DISCOVERY.md](LARAVEL_12_EVENT_DISCOVERY.md) for more details.
