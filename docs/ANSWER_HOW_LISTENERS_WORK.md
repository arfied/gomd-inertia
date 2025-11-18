# How Event Listeners Are Called - Complete Answer

## Your Question
> "How are the handlers called, like OrderFulfillmentSagaHandler? I don't see it when I issued the command php artisan event:list"

## The Answer

### Why You Didn't See Them
The handlers were in **one file** with multiple classes. Laravel only discovers **one class per file**.

### The Fix
Created **7 separate listener files** in `app/Listeners/`, each with one class.

Now when you run:
```bash
php artisan event:list
```

You see all 7 listeners! ✅

## How Listeners Are Called

### Step 1: Event is Dispatched
```php
// In your code or a job:
event(new OrderCreated('order-1', ['patient_id' => 'p-1']));
```

### Step 2: Laravel Discovers Listeners
Laravel scans `app/Listeners/` and finds:
```
OrderCreated event → OrderFulfillmentSagaOrderCreatedListener
```

### Step 3: Listener's `handle()` Method is Called
```php
// Laravel automatically calls:
$listener = new OrderFulfillmentSagaOrderCreatedListener();
$listener->handle($event);
```

### Step 4: Inside the Listener
```php
class OrderFulfillmentSagaOrderCreatedListener implements ShouldQueue
{
    public function handle(OrderCreated $event): void
    {
        // This code runs when OrderCreated event is dispatched
        dispatch(new CreatePrescriptionJob(
            $event->aggregateUuid,
            $event->payload
        ))->onQueue('order-fulfillment');
    }
}
```

### Step 5: Job is Dispatched to Queue
```php
// The job is added to the 'order-fulfillment' queue
dispatch(new CreatePrescriptionJob(...))->onQueue('order-fulfillment');
```

### Step 6: Queue Worker Processes Job
```bash
php artisan queue:work --queue=order-fulfillment
```

The queue worker picks up the job and executes it.

### Step 7: Job Emits Next Event
```php
// Inside CreatePrescriptionJob:
event(new PrescriptionCreated(...));
```

### Step 8: Cycle Repeats
```
PrescriptionCreated → OrderFulfillmentSagaPrescriptionCreatedListener
                   → dispatch(ReserveInventoryJob)
                   → queue worker processes
                   → event(InventoryReserved)
                   → ... and so on
```

## The 7 Listeners

| Event | Listener | Action |
|-------|----------|--------|
| OrderCreated | OrderFulfillmentSagaOrderCreatedListener | Dispatch CreatePrescriptionJob |
| PrescriptionCreated | OrderFulfillmentSagaPrescriptionCreatedListener | Dispatch ReserveInventoryJob |
| PrescriptionFailed | OrderFulfillmentSagaPrescriptionFailedListener | Dispatch CancelOrderJob (compensation) |
| InventoryReserved | OrderFulfillmentSagaInventoryReservedListener | Dispatch InitiateShipmentJob |
| InventoryReservationFailed | OrderFulfillmentSagaInventoryReservationFailedListener | Dispatch CancelPrescriptionJob (compensation) |
| ShipmentInitiated | OrderFulfillmentSagaShipmentInitiatedListener | Mark saga complete |
| ShipmentInitiationFailed | OrderFulfillmentSagaShipmentInitiationFailedListener | Dispatch ReleaseInventoryJob (compensation) |

## File Structure

```
app/Listeners/
├── OrderFulfillmentSagaOrderCreatedListener.php
│   └── class OrderFulfillmentSagaOrderCreatedListener
│       └── public function handle(OrderCreated $event)
│
├── OrderFulfillmentSagaPrescriptionCreatedListener.php
│   └── class OrderFulfillmentSagaPrescriptionCreatedListener
│       └── public function handle(PrescriptionCreated $event)
│
└── ... (5 more listeners)
```

## Key Points

✅ **One class per file** - Laravel only discovers one class per file
✅ **In `app/Listeners/`** - Default scan location
✅ **Public `handle()` method** - Required method name
✅ **Type-hinted event** - Determines which event it listens to
✅ **Implements `ShouldQueue`** - For async processing

## Verification

```bash
# See all discovered listeners
php artisan event:list

# Test event dispatching
php artisan tinker
>>> event(new \App\Domain\Order\Events\OrderCreated('order-1', ['patient_id' => 'p-1']));

# Watch queue worker process jobs
php artisan queue:work --queue=order-fulfillment
```

## Complete Flow Example

```
1. event(new OrderCreated(...))
   ↓
2. Laravel finds: OrderFulfillmentSagaOrderCreatedListener
   ↓
3. Calls: $listener->handle($event)
   ↓
4. Listener dispatches: CreatePrescriptionJob
   ↓
5. Queue worker processes job
   ↓
6. Job emits: event(new PrescriptionCreated(...))
   ↓
7. Laravel finds: OrderFulfillmentSagaPrescriptionCreatedListener
   ↓
8. Calls: $listener->handle($event)
   ↓
9. Listener dispatches: ReserveInventoryJob
   ↓
10. ... cycle continues until saga completes
```

## Documentation

- **Discovery Details**: [HOW_LISTENERS_ARE_DISCOVERED.md](HOW_LISTENERS_ARE_DISCOVERED.md)
- **What Was Fixed**: [LISTENER_DISCOVERY_FIXED.md](LISTENER_DISCOVERY_FIXED.md)
- **Setup Guide**: [SETUP_GUIDE_LARAVEL_12.md](SETUP_GUIDE_LARAVEL_12.md)
