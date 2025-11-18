# How Event Listeners Are Discovered in Laravel 12

## The Problem You Encountered

You ran `php artisan event:list` but didn't see the saga listeners. Here's why and how it's fixed.

## Why It Wasn't Working

The handlers were all in **one file** (`OrderFulfillmentSagaHandler.php`):
```php
// ❌ WRONG: Multiple classes in one file
class OrderFulfillmentSagaHandler { }
class PrescriptionCreatedHandler { }
class InventoryReservedHandler { }
// ... etc
```

Laravel's auto-discovery **only finds one class per file**.

## How It Works Now ✅

Each listener is in its **own file** in `app/Listeners/`:

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

## Discovery Process

Laravel 12 automatically discovers listeners by:

1. **Scanning `app/Listeners` directory**
   - Looks for all PHP files

2. **Finding the class in each file**
   - One class per file

3. **Checking for `handle()` or `__invoke()` method**
   - Must be public method

4. **Reading the type-hint**
   - Determines which event it listens to

5. **Registering automatically**
   - No manual registration needed!

## Example: OrderCreated Event

### File: `app/Listeners/OrderFulfillmentSagaOrderCreatedListener.php`

```php
class OrderFulfillmentSagaOrderCreatedListener implements ShouldQueue
{
    public function handle(OrderCreated $event): void
    {
        // Laravel sees: OrderCreated type-hint
        // Automatically registers this listener for OrderCreated events
        dispatch(new CreatePrescriptionJob(...));
    }
}
```

### What Laravel Does

1. Scans `app/Listeners/OrderFulfillmentSagaOrderCreatedListener.php`
2. Finds class `OrderFulfillmentSagaOrderCreatedListener`
3. Finds method `handle(OrderCreated $event)`
4. Reads type-hint: `OrderCreated`
5. Registers: When `OrderCreated` event is dispatched, call this listener

## Verification

```bash
php artisan event:list
```

Output shows all 7 listeners:
```
OrderCreated
  ⇂ App\Listeners\OrderFulfillmentSagaOrderCreatedListener@handle (ShouldQueue)

PrescriptionCreated
  ⇂ App\Listeners\OrderFulfillmentSagaPrescriptionCreatedListener@handle (ShouldQueue)

PrescriptionFailed
  ⇂ App\Listeners\OrderFulfillmentSagaPrescriptionFailedListener@handle (ShouldQueue)

InventoryReserved
  ⇂ App\Listeners\OrderFulfillmentSagaInventoryReservedListener@handle (ShouldQueue)

InventoryReservationFailed
  ⇂ App\Listeners\OrderFulfillmentSagaInventoryReservationFailedListener@handle (ShouldQueue)

ShipmentInitiated
  ⇂ App\Listeners\OrderFulfillmentSagaShipmentInitiatedListener@handle (ShouldQueue)

ShipmentInitiationFailed
  ⇂ App\Listeners\OrderFulfillmentSagaShipmentInitiationFailedListener@handle (ShouldQueue)
```

## How Events Are Called

### 1. Event is Dispatched
```php
event(new OrderCreated('order-1', ['patient_id' => 'p-1']));
```

### 2. Laravel Finds Registered Listeners
```
OrderCreated event → OrderFulfillmentSagaOrderCreatedListener
```

### 3. Listener's `handle()` Method is Called
```php
$listener = new OrderFulfillmentSagaOrderCreatedListener();
$listener->handle($event);
```

### 4. Job is Dispatched
```php
dispatch(new CreatePrescriptionJob(...));
```

### 5. Queue Worker Processes Job
```bash
php artisan queue:work --queue=order-fulfillment
```

## Key Rules for Auto-Discovery

✅ **One class per file** - Multiple classes won't be discovered
✅ **In `app/Listeners` directory** - Default scan location
✅ **Public `handle()` method** - Required method name
✅ **Type-hinted event parameter** - Determines which event it listens to
✅ **Implements `ShouldQueue`** - For async processing (optional but recommended)

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

## Testing

```bash
# Verify listeners are discovered
php artisan event:list

# Test event dispatching
php artisan tinker
>>> event(new \App\Domain\Order\Events\OrderCreated('order-1', ['patient_id' => 'p-1']));

# Check queue worker processes the job
php artisan queue:work --queue=order-fulfillment
```

## Summary

**Before**: Multiple classes in one file → Not discovered
**After**: One class per file in `app/Listeners/` → Automatically discovered

That's why you didn't see the listeners before, and why they appear now! ✅
