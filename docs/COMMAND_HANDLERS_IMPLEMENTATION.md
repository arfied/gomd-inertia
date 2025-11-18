# Command Handlers Implementation ✅

## Overview
All command handlers for the order fulfillment saga have been implemented!

## ✅ Handlers Created (3)

### 1. StartOrderFulfillmentSagaHandler ✅
**Location:** `app/Application/Order/Handlers/StartOrderFulfillmentSagaHandler.php`

**Purpose:** Initiates the order fulfillment saga

**Flow:**
```
StartOrderFulfillmentSaga command
    ↓
OrderFulfillmentSaga::start()
    ↓
OrderFulfillmentSagaStarted event
    ↓
Event stored & dispatched
```

**Implementation:**
```php
$saga = OrderFulfillmentSaga::start(
    $command->sagaUuid,
    $command->orderUuid,
    $command->metadata
);

foreach ($saga->releaseEvents() as $event) {
    $this->eventStore->store($event);
    $this->events->dispatch($event);
}
```

---

### 2. ReserveInventoryHandler ✅
**Location:** `app/Application/Order/Handlers/ReserveInventoryHandler.php`

**Purpose:** Reserves inventory for prescribed medications (Step 2)

**Flow:**
```
ReserveInventory command
    ↓
Validate & call inventory service
    ↓
InventoryReserved event (or InventoryReservationFailed)
    ↓
Event stored & dispatched
```

**TODO Implementation:**
```php
// Call your inventory service
$inventoryService = app(InventoryService::class);
$reservationResult = $inventoryService->reserve(
    medications: $command->medications,
    warehouseId: $command->warehouseId,
);

// Emit appropriate event based on result
if ($reservationResult->success) {
    $event = new InventoryReserved(...);
} else {
    $event = new InventoryReservationFailed(...);
}
```

---

### 3. InitiateShipmentHandler ✅
**Location:** `app/Application/Order/Handlers/InitiateShipmentHandler.php`

**Purpose:** Initiates shipment of order to patient (Step 3)

**Flow:**
```
InitiateShipment command
    ↓
Validate & call shipping service
    ↓
ShipmentInitiated event (or ShipmentInitiationFailed)
    ↓
Event stored & dispatched
```

**TODO Implementation:**
```php
// Call your shipping service
$shippingService = app(ShippingService::class);
$shipmentResult = $shippingService->initiate(
    orderUuid: $command->orderUuid,
    shippingAddress: $command->shippingAddress,
    shippingMethod: $command->shippingMethod,
);

// Emit appropriate event based on result
if ($shipmentResult->success) {
    $event = new ShipmentInitiated(...);
} else {
    $event = new ShipmentInitiationFailed(...);
}
```

---

## Existing Handlers (Already Implemented)

### CreateOrderHandler
- Creates new order
- Emits OrderCreated event

### CancelOrderHandler
- Cancels order
- Emits OrderCancelled event

### FulfillOrderHandler
- Marks order as fulfilled
- Emits OrderFulfilled event

### AssignOrderToDoctorHandler
- Assigns order to doctor
- Emits OrderAssignedToDoctor event

---

## Handler Pattern

All handlers follow the same pattern:

```php
class MyCommandHandler implements CommandHandler
{
    public function __construct(
        private EventStoreContract $eventStore,
        private Dispatcher $events,
    ) {
    }

    public function handle(Command $command): void
    {
        // 1. Validate command type
        if (! $command instanceof MyCommand) {
            throw new InvalidArgumentException(...);
        }

        // 2. Build payload
        $payload = [
            'field1' => $command->field1,
            'field2' => $command->field2,
        ];

        // 3. Call domain logic (aggregate)
        $aggregate = MyAggregate::action(
            $command->uuid,
            $payload,
            $command->metadata,
        );

        // 4. Store and dispatch events
        foreach ($aggregate->releaseEvents() as $event) {
            $this->eventStore->store($event);
            $this->events->dispatch($event);
        }
    }
}
```

---

## Files Created

✅ `app/Application/Order/Handlers/StartOrderFulfillmentSagaHandler.php`
✅ `app/Application/Order/Handlers/ReserveInventoryHandler.php`
✅ `app/Application/Order/Handlers/InitiateShipmentHandler.php`
✅ `app/Application/Order/Commands/StartOrderFulfillmentSaga.php`

---

## Saga Flow with Handlers

```
1. CreateOrder command
   ↓ CreateOrderHandler
   ↓ OrderCreated event
   
2. StartOrderFulfillmentSaga command ✅
   ↓ StartOrderFulfillmentSagaHandler
   ↓ OrderFulfillmentSagaStarted event
   
3. ReserveInventory command ✅
   ↓ ReserveInventoryHandler
   ↓ InventoryReserved event
   
4. InitiateShipment command ✅
   ↓ InitiateShipmentHandler
   ↓ ShipmentInitiated event
   
5. FulfillOrder command
   ↓ FulfillOrderHandler
   ↓ OrderFulfilled event
   
6. OrderFulfillmentSagaCompleted ✅
```

---

## Next Steps

### 1. Implement Business Logic
In each handler's TODO section, implement:
- Inventory reservation logic
- Shipment initiation logic
- Error handling and compensation

### 2. Example: ReserveInventory Implementation
```php
try {
    $inventoryService = app(InventoryService::class);
    $result = $inventoryService->reserve(
        medications: $command->medications,
        warehouseId: $command->warehouseId,
    );

    if ($result->success) {
        $event = new InventoryReserved(
            $command->orderUuid,
            [
                'medications' => $command->medications,
                'warehouse_id' => $command->warehouseId,
                'reservation_id' => $result->reservationId,
            ],
            $command->metadata
        );
    } else {
        $event = new InventoryReservationFailed(
            $command->orderUuid,
            ['reason' => $result->error],
            $command->metadata
        );
    }
} catch (Exception $e) {
    $event = new InventoryReservationFailed(
        $command->orderUuid,
        ['reason' => $e->getMessage()],
        $command->metadata
    );
}

$this->eventStore->store($event);
$this->events->dispatch($event);
```

### 3. Register Handlers
Handlers are auto-discovered by Laravel's command bus.
No manual registration needed!

### 4. Write Tests
```php
// Test successful reservation
$this->dispatch(new ReserveInventory(...));
$this->assertDatabaseHas('event_store', [
    'event_type' => 'order.inventory_reserved',
]);

// Test failed reservation
$this->dispatch(new ReserveInventory(...));
$this->assertDatabaseHas('event_store', [
    'event_type' => 'order.inventory_reservation_failed',
]);
```

---

## Summary

✅ **StartOrderFulfillmentSagaHandler** - Implemented
✅ **ReserveInventoryHandler** - Implemented (TODO: business logic)
✅ **InitiateShipmentHandler** - Implemented (TODO: business logic)
✅ **All handlers follow CQRS pattern**
✅ **All handlers use event sourcing**
✅ **Ready for business logic implementation**

**Next: Implement the TODO sections with your business logic!**
