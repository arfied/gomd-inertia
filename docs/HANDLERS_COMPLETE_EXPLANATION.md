# How Handlers in Application Folder Are Used - Complete Explanation

## Quick Answer

Handlers in `app/Application/Order/Handlers/` are **command processors** that:
1. Receive commands from controllers, jobs, or sagas
2. Execute business logic via services
3. Create domain events
4. Store events in the event store
5. Dispatch events to listeners

They implement the **CQRS (Command Query Responsibility Segregation)** pattern with **event sourcing**.

---

## The Complete Flow

### Step 1: Handler Registration
**File:** `app/Providers/AppServiceProvider.php`

```php
$this->app->resolving(CommandBus::class, function (CommandBus $bus, $app) {
    $bus->register(
        ReserveInventory::class,
        $app->make(ReserveInventoryHandler::class)
    );
});
```

### Step 2: Command Creation
**File:** `app/Http/Controllers/OrderFulfillmentController.php`

```php
$command = new ReserveInventory(
    orderUuid: 'order-123',
    sagaUuid: 'saga-123',
    medications: [['medication_id' => 1, 'quantity' => 30]],
    warehouseId: 'warehouse-1',
);
```

### Step 3: Command Dispatch
**File:** `app/Http/Controllers/OrderFulfillmentController.php`

```php
$commandBus->dispatch($command);
```

### Step 4: Handler Lookup
**File:** `app/Application/Commands/CommandBus.php`

```php
public function dispatch(Command $command): void
{
    $commandClass = $command::class; // ReserveInventory
    $handler = $this->handlers[$commandClass]; // ReserveInventoryHandler
    $handler->handle($command);
}
```

### Step 5: Handler Execution
**File:** `app/Application/Order/Handlers/ReserveInventoryHandler.php`

```php
public function handle(Command $command): void
{
    // 1. Call business logic
    $result = $this->inventoryService->reserve(
        medications: $command->medications,
        warehouseId: $command->warehouseId,
    );

    // 2. Create event
    if ($result['success']) {
        $event = new InventoryReserved($command->orderUuid, $payload, ...);
    } else {
        $event = new InventoryReservationFailed($command->orderUuid, $payload, ...);
    }

    // 3. Store event
    $this->eventStore->store($event);

    // 4. Dispatch event
    $this->events->dispatch($event);
}
```

### Step 6: Event Storage
**Database:** `stored_events` table

```
id | aggregate_id | event_type | payload | created_at
1  | order-123    | InventoryReserved | {...} | 2024-11-18
```

### Step 7: Event Listeners Triggered
**File:** `app/Listeners/OrderFulfillmentSagaInventoryReservedListener.php`

```php
public function handle(InventoryReserved $event): void
{
    // Update read models
    // Trigger next saga step
    // Queue jobs
}
```

### Step 8: Read Models Updated
**Database:** `inventory_reservations` table

```
id | reservation_id | status | medications | reserved_at
1  | RES-uuid       | reserved | [...] | 2024-11-18
```

---

## Handler Architecture

### Handler Interface
```php
interface CommandHandler
{
    public function handle(Command $command): void;
}
```

### Handler Dependencies
```php
class ReserveInventoryHandler implements CommandHandler
{
    public function __construct(
        private EventStoreContract $eventStore,        // Store events
        private Dispatcher $events,                     // Dispatch events
        private InventoryReservationService $service,  // Business logic
    ) {
    }
}
```

### Handler Responsibilities
1. **Validate** command type
2. **Call** business logic service
3. **Create** domain event(s)
4. **Store** event in EventStore
5. **Dispatch** event to listeners
6. **Handle** errors gracefully

---

## Usage Contexts

### Context 1: HTTP Controller
```php
public function update(Request $request, CommandBus $commandBus)
{
    $command = new ReserveInventory(...);
    $commandBus->dispatch($command);
}
```

### Context 2: Queue Job
```php
public function handle(CommandBus $commandBus)
{
    $command = new ReserveInventory(...);
    $commandBus->dispatch($command);
}
```

### Context 3: Event Listener
```php
public function handle(OrderCreated $event, CommandBus $commandBus)
{
    $command = new ReserveInventory(...);
    $commandBus->dispatch($command);
}
```

### Context 4: Saga Orchestrator
```php
public function reserveInventory(CommandBus $commandBus)
{
    $command = new ReserveInventory(...);
    $commandBus->dispatch($command);
}
```

---

## New Handlers for Order Fulfillment Saga

### 1. ReserveInventoryHandler
- **Location:** `app/Application/Order/Handlers/ReserveInventoryHandler.php`
- **Command:** `ReserveInventory`
- **Service:** `InventoryReservationService`
- **Success Event:** `InventoryReserved`
- **Failure Event:** `InventoryReservationFailed`
- **Purpose:** Reserve medications from inventory

### 2. InitiateShipmentHandler
- **Location:** `app/Application/Order/Handlers/InitiateShipmentHandler.php`
- **Command:** `InitiateShipment`
- **Service:** `ShipmentInitiationService`
- **Success Event:** `ShipmentInitiated`
- **Failure Event:** `ShipmentInitiationFailed`
- **Purpose:** Create shipment and generate tracking

### 3. StartOrderFulfillmentSagaHandler
- **Location:** `app/Application/Order/Handlers/StartOrderFulfillmentSagaHandler.php`
- **Command:** `StartOrderFulfillmentSaga`
- **Service:** None (uses aggregate)
- **Event:** `OrderFulfillmentSagaStarted`
- **Purpose:** Initiate saga orchestration

---

## Key Files

| File | Purpose |
|------|---------|
| `app/Application/Commands/CommandBus.php` | Routes commands to handlers |
| `app/Providers/AppServiceProvider.php` | Registers handlers |
| `app/Application/Order/Handlers/*` | Handler implementations |
| `app/Services/*Service.php` | Business logic |
| `app/Domain/Order/Events/*` | Domain events |
| `app/Listeners/*` | Event listeners |

---

## Event Flow Summary

```
Command Created
    ↓
CommandBus.dispatch()
    ↓
Handler.handle()
    ↓
Service.execute()
    ↓
Event Created
    ↓
EventStore.store()
    ↓
Dispatcher.dispatch()
    ↓
Listeners Triggered
    ↓
Read Models Updated
    ↓
Queue Jobs Queued
```

---

## CQRS Pattern

**Command (Write):**
- Handler receives command
- Executes business logic
- Creates and stores event
- Dispatches event

**Query (Read):**
- Separate query handlers
- Read from projections
- No side effects
- Fast queries

---

## Event Sourcing Pattern

**All state changes are events:**
- Events are immutable
- Events are stored in order
- Events can be replayed
- Complete audit trail
- Temporal queries possible

---

## Summary

**Handlers are the core of CQRS:**
- ✅ Process commands
- ✅ Execute business logic
- ✅ Create events
- ✅ Store events
- ✅ Dispatch events
- ✅ Trigger side effects

**They connect:**
- Controllers → Commands → Handlers → Services → Events → Listeners → Read Models

See related docs:
- `HOW_HANDLERS_ARE_USED.md` - Detailed explanation
- `USING_NEW_HANDLERS_EXAMPLE.md` - Practical examples
- `HANDLERS_USAGE_SUMMARY.md` - Quick reference
