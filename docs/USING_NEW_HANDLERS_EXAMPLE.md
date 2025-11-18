# Using New Handlers - Practical Examples

## Overview

The new handlers (`ReserveInventoryHandler` and `InitiateShipmentHandler`) are part of the order fulfillment saga. They must be registered in `AppServiceProvider` and dispatched via `CommandBus`.

## Step 1: Register Handlers in AppServiceProvider

**File:** `app/Providers/AppServiceProvider.php`

Add these registrations in the `resolving` callback:

```php
$this->app->resolving(CommandBus::class, function (CommandBus $bus, $app) {
    // ... existing registrations ...

    // Add these new registrations:
    $bus->register(
        ReserveInventory::class,
        $app->make(ReserveInventoryHandler::class)
    );

    $bus->register(
        InitiateShipment::class,
        $app->make(InitiateShipmentHandler::class)
    );

    $bus->register(
        StartOrderFulfillmentSaga::class,
        $app->make(StartOrderFulfillmentSagaHandler::class)
    );
});
```

Also add imports at the top:

```php
use App\Application\Order\Commands\ReserveInventory;
use App\Application\Order\Commands\InitiateShipment;
use App\Application\Order\Commands\StartOrderFulfillmentSaga;
use App\Application\Order\Handlers\ReserveInventoryHandler;
use App\Application\Order\Handlers\InitiateShipmentHandler;
use App\Application\Order\Handlers\StartOrderFulfillmentSagaHandler;
```

## Step 2: Use in Controller

**Example:** Create an order fulfillment controller

```php
<?php

namespace App\Http\Controllers;

use App\Application\Commands\CommandBus;
use App\Application\Order\Commands\ReserveInventory;
use App\Application\Order\Commands\InitiateShipment;
use App\Application\Order\Commands\StartOrderFulfillmentSaga;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderFulfillmentController extends Controller
{
    public function startSaga(Request $request, CommandBus $commandBus): JsonResponse
    {
        $data = $request->validate([
            'order_uuid' => 'required|uuid',
            'medications' => 'required|array',
            'shipping_address' => 'required|string',
        ]);

        // 1. Start saga
        $sagaUuid = \Illuminate\Support\Str::uuid();
        $commandBus->dispatch(new StartOrderFulfillmentSaga(
            sagaUuid: $sagaUuid,
            orderUuid: $data['order_uuid'],
            metadata: ['source' => 'api'],
        ));

        // 2. Reserve inventory
        $commandBus->dispatch(new ReserveInventory(
            orderUuid: $data['order_uuid'],
            sagaUuid: $sagaUuid,
            medications: $data['medications'],
            warehouseId: 'warehouse-1',
            metadata: ['source' => 'api'],
        ));

        // 3. Initiate shipment
        $commandBus->dispatch(new InitiateShipment(
            orderUuid: $data['order_uuid'],
            sagaUuid: $sagaUuid,
            shippingAddress: $data['shipping_address'],
            shippingMethod: 'standard',
            metadata: ['source' => 'api'],
        ));

        return response()->json([
            'message' => 'Order fulfillment saga started',
            'saga_uuid' => $sagaUuid,
        ]);
    }
}
```

## Step 3: Use in Queue Job

**Example:** Queue job for async processing

```php
<?php

namespace App\Jobs;

use App\Application\Commands\CommandBus;
use App\Application\Order\Commands\ReserveInventory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ReserveInventoryJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private string $orderUuid,
        private string $sagaUuid,
        private array $medications,
        private ?string $warehouseId = null,
    ) {
    }

    public function handle(CommandBus $commandBus): void
    {
        $commandBus->dispatch(new ReserveInventory(
            orderUuid: $this->orderUuid,
            sagaUuid: $this->sagaUuid,
            medications: $this->medications,
            warehouseId: $this->warehouseId,
            metadata: ['source' => 'queue_job'],
        ));
    }
}
```

## Step 4: Use in Event Listener

**Example:** Trigger inventory reservation on order created

```php
<?php

namespace App\Listeners;

use App\Application\Commands\CommandBus;
use App\Application\Order\Commands\ReserveInventory;
use App\Domain\Order\Events\OrderCreated;
use Illuminate\Support\Str;

class ReserveInventoryOnOrderCreated
{
    public function __construct(private CommandBus $commandBus)
    {
    }

    public function handle(OrderCreated $event): void
    {
        // Get medications from order
        $medications = $event->payload['medications'] ?? [];

        if (empty($medications)) {
            return;
        }

        // Dispatch reserve inventory command
        $this->commandBus->dispatch(new ReserveInventory(
            orderUuid: $event->aggregateId,
            sagaUuid: Str::uuid(),
            medications: $medications,
            warehouseId: 'warehouse-1',
            metadata: ['source' => 'event_listener'],
        ));
    }
}
```

## Step 5: Monitor Events

After handlers execute, check the database:

```bash
# Check stored events
php artisan tinker
>>> DB::table('stored_events')->latest()->first();

# Check inventory reservations
>>> DB::table('inventory_reservations')->latest()->first();

# Check shipments
>>> DB::table('shipments')->latest()->first();
```

## Step 6: Test the Flow

```php
// In tinker or test
$commandBus = app(\App\Application\Commands\CommandBus::class);

// Create and dispatch command
$command = new ReserveInventory(
    orderUuid: 'order-123',
    sagaUuid: 'saga-123',
    medications: [
        ['medication_id' => 1, 'quantity' => 30],
    ],
    warehouseId: 'warehouse-1',
);

$commandBus->dispatch($command);

// Check results
DB::table('inventory_reservations')->where('reservation_id', 'like', 'RES-%')->latest()->first();
DB::table('stored_events')->where('event_type', 'InventoryReserved')->latest()->first();
```

## Complete Flow Example

```php
// 1. Create order
$order = Order::create([...]);

// 2. Start saga
$sagaUuid = Str::uuid();
$commandBus->dispatch(new StartOrderFulfillmentSaga(
    sagaUuid: $sagaUuid,
    orderUuid: $order->uuid,
));

// 3. Reserve inventory
$commandBus->dispatch(new ReserveInventory(
    orderUuid: $order->uuid,
    sagaUuid: $sagaUuid,
    medications: $order->medications,
));

// 4. Initiate shipment
$commandBus->dispatch(new InitiateShipment(
    orderUuid: $order->uuid,
    sagaUuid: $sagaUuid,
    shippingAddress: $order->shipping_address,
));

// 5. Events are stored and dispatched
// 6. Listeners respond to events
// 7. Read models updated
// 8. Queue jobs triggered
```

## Key Points

✅ **Register handlers** in `AppServiceProvider`
✅ **Inject CommandBus** in controllers/jobs
✅ **Create commands** with required data
✅ **Dispatch commands** via `commandBus->dispatch()`
✅ **Handlers execute** business logic
✅ **Events stored** in event store
✅ **Listeners triggered** automatically
✅ **Read models updated** via projections

## Troubleshooting

**Handler not found error:**
- Check handler is registered in `AppServiceProvider`
- Verify command class name matches registration

**Events not stored:**
- Check `EventStore` is properly injected
- Verify `stored_events` table exists

**Listeners not triggered:**
- Check listeners are in `app/Listeners/`
- Verify event class name matches listener type hint
- Run `php artisan event:list` to verify discovery
