# Order Fulfillment Saga - Events & Commands Complete ✅

## Summary
All domain events and commands for the order fulfillment saga are now complete and compatible with your event sourcing setup!

## ✅ Domain Events (17 total)

### Saga Lifecycle Events
Located in `app/Domain/Order/Events/`

| Event | Event Type | Aggregate Type | Purpose |
|-------|-----------|----------------|---------|
| `OrderFulfillmentSagaStarted` | `order_fulfillment_saga.started` | `order_fulfillment_saga` | Saga begins |
| `OrderFulfillmentSagaStateChanged` | `order_fulfillment_saga.state_changed` | `order_fulfillment_saga` | State transition |
| `OrderFulfillmentSagaCompleted` | `order_fulfillment_saga.completed` | `order_fulfillment_saga` | Saga completes |
| `OrderFulfillmentSagaFailed` | `order_fulfillment_saga.failed` | `order_fulfillment_saga` | Saga fails |

### Step Events (Happy Path)
| Event | Event Type | Aggregate Type | Purpose |
|-------|-----------|----------------|---------|
| `OrderCreated` | `order.created` | `order` | Order created (triggers saga) |
| `PrescriptionCreated` | `order.prescription_created` | `order` | Prescription created (step 1) |
| `InventoryReserved` | `order.inventory_reserved` | `order` | Inventory reserved (step 2) |
| `ShipmentInitiated` | `order.shipment_initiated` | `order` | Shipment initiated (step 3) |
| `OrderFulfilled` | `order.fulfilled` | `order` | Order fulfilled (complete) |

### Failure Events
| Event | Event Type | Aggregate Type | Purpose |
|-------|-----------|----------------|---------|
| `PrescriptionFailed` | `order.prescription_failed` | `order` | Prescription creation failed |
| `InventoryReservationFailed` | `order.inventory_reservation_failed` | `order` | Inventory reservation failed |
| `ShipmentInitiationFailed` | `order.shipment_initiation_failed` | `order` | Shipment initiation failed |

### Compensation Events
| Event | Event Type | Aggregate Type | Purpose |
|-------|-----------|----------------|---------|
| `PrescriptionCancelled` | `order.prescription_cancelled` | `order` | Prescription cancelled (compensation) |
| `InventoryReleased` | `order.inventory_released` | `order` | Inventory released (compensation) |
| `OrderCancelled` | `order.cancelled` | `order` | Order cancelled (compensation) |
| `CompensationRecorded` | `order.compensation_recorded` | `order` | Compensation action recorded |

## ✅ Commands (6 total)

### Location
`app/Application/Order/Commands/`

### Existing Commands (4)
1. **CreateOrder** - Creates a new order
   - Parameters: orderUuid, patientId, doctorId, prescriptionId, patientNotes, doctorNotes, metadata

2. **CancelOrder** - Cancels an order
   - Parameters: orderUuid, reason, metadata

3. **FulfillOrder** - Marks order as fulfilled
   - Parameters: orderUuid, metadata

4. **AssignOrderToDoctor** - Assigns order to a doctor
   - Parameters: orderUuid, doctorId, assignedByUserId, metadata

### New Saga Commands (2) ✅ CREATED
5. **ReserveInventory** - Reserves inventory for medications
   - Parameters: orderUuid, sagaUuid, medications, warehouseId, metadata
   - Purpose: Step 2 of saga - reserves inventory after prescription created

6. **InitiateShipment** - Initiates shipment of order
   - Parameters: orderUuid, sagaUuid, shippingAddress, shippingMethod, trackingNumber, metadata
   - Purpose: Step 3 of saga - initiates shipment after inventory reserved

## 📊 Event Sourcing Compatibility

All events and commands are compatible with your existing setup:

### Event Structure
```php
class OrderFulfillmentSagaStarted extends DomainEvent
{
    public static function eventType(): string
    {
        return 'order_fulfillment_saga.started';
    }

    public static function aggregateType(): string
    {
        return 'order_fulfillment_saga';
    }
}
```

### Command Structure
```php
class ReserveInventory implements Command
{
    public function __construct(
        public string $orderUuid,
        public string $sagaUuid,
        public array $medications,
        public ?string $warehouseId = null,
        public array $metadata = [],
    ) {
    }
}
```

### JSON Serialization
All events use the base `DomainEvent` class which provides:
- `toStoredEventAttributes()` - Converts to JSON-serializable array
- `store()` - Persists to event_store table
- Automatic JSON serialization of payload and metadata

## 🔄 Saga Flow with Events & Commands

### Happy Path
```
1. CreateOrder command
   ↓
2. OrderCreated event
   ↓
3. ReserveInventory command
   ↓
4. InventoryReserved event
   ↓
5. InitiateShipment command
   ↓
6. ShipmentInitiated event
   ↓
7. OrderFulfilled event
   ↓
8. OrderFulfillmentSagaCompleted event ✅
```

### Failure Path (Inventory Fails)
```
1. InventoryReservationFailed event
   ↓
2. CancelOrder command (compensation)
   ↓
3. OrderCancelled event
   ↓
4. OrderFulfillmentSagaFailed event ❌
```

## 📁 File Structure

### Events
```
app/Domain/Order/Events/
├── OrderFulfillmentSagaStarted.php
├── OrderFulfillmentSagaStateChanged.php
├── OrderFulfillmentSagaCompleted.php
├── OrderFulfillmentSagaFailed.php
├── OrderCreated.php
├── PrescriptionCreated.php
├── PrescriptionFailed.php
├── InventoryReserved.php
├── InventoryReservationFailed.php
├── ShipmentInitiated.php
├── ShipmentInitiationFailed.php
├── OrderFulfilled.php
├── PrescriptionCancelled.php
├── InventoryReleased.php
├── OrderCancelled.php
└── CompensationRecorded.php
```

### Commands
```
app/Application/Order/Commands/
├── CreateOrder.php
├── CancelOrder.php
├── FulfillOrder.php
├── AssignOrderToDoctor.php
├── ReserveInventory.php ✅ NEW
└── InitiateShipment.php ✅ NEW
```

## 🚀 Usage Example

### Dispatching Commands
```php
// Step 1: Create order
dispatch(new CreateOrder(
    orderUuid: 'order-123',
    patientId: 456,
    doctorId: 789,
    metadata: ['source' => 'api']
));

// Step 2: Reserve inventory (after prescription created)
dispatch(new ReserveInventory(
    orderUuid: 'order-123',
    sagaUuid: 'saga-123',
    medications: [
        ['medication_id' => 1, 'quantity' => 30],
        ['medication_id' => 2, 'quantity' => 60],
    ],
    warehouseId: 'warehouse-1',
    metadata: ['source' => 'saga']
));

// Step 3: Initiate shipment (after inventory reserved)
dispatch(new InitiateShipment(
    orderUuid: 'order-123',
    sagaUuid: 'saga-123',
    shippingAddress: '123 Main St, City, State 12345',
    shippingMethod: 'standard',
    metadata: ['source' => 'saga']
));
```

### Event Persistence
```php
// Events are automatically stored in event_store table
$event = new OrderCreated('order-123', [
    'patient_id' => 456,
    'doctor_id' => 789,
    'medications' => [...]
]);

$storedEvent = $event->store();

// Stored as JSON:
// {
//   "aggregate_uuid": "order-123",
//   "aggregate_type": "order",
//   "event_type": "order.created",
//   "event_data": {...},
//   "metadata": {...},
//   "occurred_at": "2025-11-18 11:00:00.000000"
// }
```

## ✨ Key Features

✅ **Event Sourcing Compatible** - Uses DomainEvent base class
✅ **JSON Serialization** - Automatic JSON serialization of payload/metadata
✅ **CQRS Pattern** - Commands on write side, events on read side
✅ **Saga Pattern** - Distributed transactions with compensation
✅ **Type-Safe** - Constructor property promotion for type safety
✅ **Metadata Support** - Tracing and debugging metadata
✅ **Aggregate Types** - Proper aggregate type identification

## 📝 Next Steps

1. ✅ Domain Events created (17 total)
2. ✅ Commands created (6 total)
3. ⏳ Implement command handlers
4. ⏳ Implement event listeners
5. ⏳ Implement queue jobs
6. ⏳ Write tests

## Summary

**All domain events and commands for the order fulfillment saga are complete!**

- ✅ 17 Domain Events (saga lifecycle, steps, failures, compensation)
- ✅ 6 Commands (4 existing + 2 new saga commands)
- ✅ Full JSON serialization support
- ✅ Compatible with existing event sourcing setup
- ✅ Ready for command handlers and listeners
