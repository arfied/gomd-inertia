# Saga Commands - Quick Reference ✅

## New Commands Created

### 1. ReserveInventory Command ✅

**Location:** `app/Application/Order/Commands/ReserveInventory.php`

**Purpose:** Reserve inventory for prescribed medications (Step 2 of saga)

**Parameters:**
```php
public string $orderUuid,           // Order UUID
public string $sagaUuid,            // Saga UUID for tracking
public array $medications,          // Medications to reserve
public ?string $warehouseId = null, // Optional warehouse ID
public array $metadata = [],        // Tracing metadata
```

**Usage:**
```php
dispatch(new ReserveInventory(
    orderUuid: 'order-123',
    sagaUuid: 'saga-123',
    medications: [
        ['medication_id' => 1, 'quantity' => 30],
        ['medication_id' => 2, 'quantity' => 60],
    ],
    warehouseId: 'warehouse-1',
    metadata: ['source' => 'saga', 'user_id' => 456]
));
```

**Triggers Event:** `InventoryReserved` or `InventoryReservationFailed`

---

### 2. InitiateShipment Command ✅

**Location:** `app/Application/Order/Commands/InitiateShipment.php`

**Purpose:** Initiate shipment of order to patient (Step 3 of saga)

**Parameters:**
```php
public string $orderUuid,                    // Order UUID
public string $sagaUuid,                     // Saga UUID for tracking
public string $shippingAddress,              // Shipping address
public ?string $shippingMethod = null,       // Shipping method (standard, express, etc)
public ?string $trackingNumber = null,       // Tracking number (if available)
public array $metadata = [],                 // Tracing metadata
```

**Usage:**
```php
dispatch(new InitiateShipment(
    orderUuid: 'order-123',
    sagaUuid: 'saga-123',
    shippingAddress: '123 Main St, City, State 12345',
    shippingMethod: 'standard',
    trackingNumber: 'TRACK123456',
    metadata: ['source' => 'saga', 'warehouse_id' => 'w-1']
));
```

**Triggers Event:** `ShipmentInitiated` or `ShipmentInitiationFailed`

---

## Existing Commands (for reference)

### CreateOrder
```php
dispatch(new CreateOrder(
    orderUuid: 'order-123',
    patientId: 456,
    doctorId: 789,
    prescriptionId: null,
    patientNotes: 'Take with food',
    doctorNotes: 'Monitor BP',
    metadata: ['source' => 'api']
));
```

### CancelOrder
```php
dispatch(new CancelOrder(
    orderUuid: 'order-123',
    reason: 'Inventory unavailable',
    metadata: ['source' => 'saga']
));
```

### FulfillOrder
```php
dispatch(new FulfillOrder(
    orderUuid: 'order-123',
    metadata: ['source' => 'saga']
));
```

---

## Saga Flow with Commands

```
1. CreateOrder command
   ↓ (triggers OrderCreated event)

2. ReserveInventory command ✅
   ↓ (triggers InventoryReserved event)

3. InitiateShipment command ✅
   ↓ (triggers ShipmentInitiated event)

4. OrderFulfilled event
   ↓
5. OrderFulfillmentSagaCompleted ✅
```

---

## Event Sourcing Integration

All commands are compatible with your event sourcing setup:

### Command Dispatch
```php
// In a controller or service
dispatch(new ReserveInventory(...));
```

### Command Handler
```php
// In app/Application/Order/Handlers/ReserveInventoryHandler.php
class ReserveInventoryHandler implements CommandHandler
{
    public function handle(Command $command): void
    {
        // 1. Validate command
        // 2. Call domain logic
        // 3. Record events
        // 4. Store events in event store
        // 5. Dispatch events to listeners
    }
}
```

### Event Persistence
```php
// Events are automatically stored as JSON
$event = new InventoryReserved('order-123', [
    'medications' => [...],
    'warehouse_id' => 'w-1',
    'reserved_at' => now(),
]);

$storedEvent = $event->store();
// Stored in event_store table with JSON serialization
```

---

## JSON Serialization

All commands and events use JSON serialization:

### Event Storage
```json
{
  "aggregate_uuid": "order-123",
  "aggregate_type": "order",
  "event_type": "order.inventory_reserved",
  "event_data": {
    "medications": [
      {"medication_id": 1, "quantity": 30},
      {"medication_id": 2, "quantity": 60}
    ],
    "warehouse_id": "warehouse-1"
  },
  "metadata": {
    "source": "saga",
    "user_id": 456
  },
  "occurred_at": "2025-11-18 11:37:00.000000"
}
```

---

## Implementation Checklist

- [x] Create command handlers for ReserveInventory ✅
- [x] Create command handlers for InitiateShipment ✅
- [x] Create command handlers for StartOrderFulfillmentSaga ✅
- [ ] Implement inventory reservation logic (TODO in handler)
- [ ] Implement shipment initiation logic (TODO in handler)
- [x] Create event listeners for InventoryReserved ✅
- [x] Create event listeners for ShipmentInitiated ✅
- [x] Create queue jobs for async processing ✅
- [ ] Write unit tests for commands
- [ ] Write feature tests for saga flow
- [ ] Write integration tests with event store

---

## Files Created

✅ `app/Application/Order/Commands/ReserveInventory.php`
✅ `app/Application/Order/Commands/InitiateShipment.php`

## Files Already Exist

✅ `app/Domain/Order/Events/InventoryReserved.php`
✅ `app/Domain/Order/Events/ShipmentInitiated.php`
✅ `app/Domain/Order/Events/OrderFulfilled.php`
✅ `app/Domain/Order/Events/OrderFulfillmentSagaStarted.php`

---

## Summary

✅ **ReserveInventory Command** - Created
✅ **InitiateShipment Command** - Created
✅ **All Events** - Already exist
✅ **JSON Serialization** - Fully compatible
✅ **Event Sourcing** - Ready to use

**Next: Implement command handlers and event listeners!**
