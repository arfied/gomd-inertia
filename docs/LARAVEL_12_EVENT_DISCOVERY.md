# Laravel 12 Automatic Event Discovery

## How It Works

Laravel 12 automatically discovers event listeners without manual registration. No need to edit `EventServiceProvider`!

### Discovery Rules

Laravel scans your application and automatically registers any listener class that:

1. **Has a `handle()` or `__invoke()` method**
2. **Type-hints an event in the method signature**

Example:
```php
class OrderCreatedListener
{
    public function handle(OrderCreated $event): void
    {
        // This method is automatically registered for OrderCreated events
    }
}
```

### Default Scan Directories

By default, Laravel scans:
- `app/Listeners`
- Any directory configured in `config/event.php`

## Saga Event Listeners

The saga implementation includes 7 event listeners in `app/Listeners/`:

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

Each listener has a `handle()` method that type-hints its event:

```php
class OrderFulfillmentSagaOrderCreatedListener implements ShouldQueue
{
    use Queueable;

    public function handle(OrderCreated $event): void
    {
        // Automatically registered for OrderCreated events
        dispatch(new CreatePrescriptionJob(...))->onQueue('order-fulfillment');
    }
}
```

## Verification

### Check Registered Listeners

```bash
php artisan event:list
```

Output:
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

### Debug Event Dispatching

```bash
# Enable event debugging
php artisan tinker

>>> Event::fake();
>>> event(new OrderCreated('order-1', ['patient_id' => 'p-1']));
>>> Event::assertDispatched(OrderCreated::class);
```

## Configuration

### Custom Listener Directories

If you want to scan additional directories, update `config/event.php`:

```php
return [
    'listeners' => [
        'paths' => [
            app_path('Listeners'),
            app_path('Application/Order/Handlers'),
        ],
    ],
];
```

### Disable Auto-Discovery

If needed, disable auto-discovery in `config/event.php`:

```php
return [
    'auto_discover_listeners' => false,
];
```

Then manually register in `EventServiceProvider`:

```php
protected $listen = [
    OrderCreated::class => [OrderFulfillmentSagaHandler::class],
    // ... etc
];
```

## Best Practices

✅ **Use `handle()` method** - Standard Laravel convention
✅ **Type-hint the event** - Required for auto-discovery
✅ **Implement `ShouldQueue`** - For async processing
✅ **Use `Queueable` trait** - For queue configuration
✅ **One listener per event** - Cleaner organization
✅ **Descriptive class names** - `EventNameHandler` pattern

## Troubleshooting

### Listener Not Registered

**Problem**: `php artisan event:list` doesn't show your listener

**Solutions**:
1. Verify method is named `handle` or `__invoke`
2. Verify event is type-hinted in method signature
3. Verify class is in a scanned directory
4. Run `php artisan cache:clear`
5. Check `config/event.php` for correct paths

### Event Not Dispatching

**Problem**: Event is dispatched but listener not called

**Solutions**:
1. Check queue worker is running: `php artisan queue:work`
2. Verify listener implements `ShouldQueue`
3. Check failed jobs: `php artisan queue:failed`
4. Check logs: `tail -f storage/logs/laravel.log`

### Multiple Listeners for Same Event

**Problem**: Need multiple listeners for one event

**Solution**: Create multiple handler classes:

```php
class OrderCreatedHandler1 implements ShouldQueue
{
    public function handle(OrderCreated $event): void { }
}

class OrderCreatedHandler2 implements ShouldQueue
{
    public function handle(OrderCreated $event): void { }
}
```

Both will be automatically registered and called.

## Migration from Manual Registration

If you have existing manual registrations in `EventServiceProvider`:

**Before:**
```php
protected $listen = [
    OrderCreated::class => [OrderFulfillmentSagaHandler::class],
];
```

**After:**
```php
// Remove from EventServiceProvider
// Create handler with handle() method
class OrderFulfillmentSagaHandler
{
    public function handle(OrderCreated $event): void { }
}
```

## References

- [Laravel 12 Event Documentation](https://laravel.com/docs/12.x/events)
- [Event Discovery](https://laravel.com/docs/12.x/events#event-discovery)
- [Queued Event Listeners](https://laravel.com/docs/12.x/events#queued-event-listeners)
