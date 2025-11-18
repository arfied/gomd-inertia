# Handlers Usage Summary

## Quick Answer: How Are Handlers Used?

Handlers in the `Application/Order/Handlers/` folder are used to **process commands** through a **Command Bus** pattern. They implement the **CQRS (Command Query Responsibility Segregation)** pattern.

## The Flow

```
1. Controller/Job/Saga creates a Command
2. Injects CommandBus from container
3. Calls commandBus->dispatch($command)
4. CommandBus looks up handler in registry
5. Handler->handle($command) executes
6. Handler calls business logic service
7. Handler creates domain event
8. Handler stores event in EventStore
9. Handler dispatches event to listeners
10. Listeners respond and update read models
```

## Key Components

### 1. Command Bus
**Location:** `app/Application/Commands/CommandBus.php`

- In-memory registry of command handlers
- Maps command classes to handler instances
- Dispatches commands to their handlers

### 2. Handler Registry
**Location:** `app/Providers/AppServiceProvider.php`

- Registers handlers in the `resolving` callback
- Maps each command to its handler
- Handlers are instantiated via dependency injection

### 3. Handlers
**Location:** `app/Application/Order/Handlers/`

- Implement `CommandHandler` interface
- Have a `handle(Command $command): void` method
- Inject dependencies via constructor
- Call business logic services
- Store and dispatch events

## Handler Responsibilities

Each handler:

1. **Validates** the command type
2. **Calls** business logic service
3. **Creates** domain event(s)
4. **Stores** events in EventStore
5. **Dispatches** events to listeners
6. **Handles** errors gracefully

## Usage Patterns

### Pattern 1: In Controllers

```php
public function update(Request $request, CommandBus $commandBus)
{
    $command = new UpdatePatientDemographics(...);
    $commandBus->dispatch($command);
}
```

### Pattern 2: In Queue Jobs

```php
public function handle(CommandBus $commandBus)
{
    $command = new ReserveInventory(...);
    $commandBus->dispatch($command);
}
```

### Pattern 3: In Event Listeners

```php
public function handle(OrderCreated $event, CommandBus $commandBus)
{
    $command = new ReserveInventory(...);
    $commandBus->dispatch($command);
}
```

### Pattern 4: In Sagas

```php
public function reserveInventory(CommandBus $commandBus)
{
    $command = new ReserveInventory(...);
    $commandBus->dispatch($command);
}
```

## New Handlers for Order Fulfillment Saga

### ReserveInventoryHandler
- **Command:** `ReserveInventory`
- **Service:** `InventoryReservationService`
- **Events:** `InventoryReserved` or `InventoryReservationFailed`
- **Purpose:** Reserve medications from inventory

### InitiateShipmentHandler
- **Command:** `InitiateShipment`
- **Service:** `ShipmentInitiationService`
- **Events:** `ShipmentInitiated` or `ShipmentInitiationFailed`
- **Purpose:** Create shipment record and generate tracking

### StartOrderFulfillmentSagaHandler
- **Command:** `StartOrderFulfillmentSaga`
- **Service:** None (uses aggregate)
- **Events:** `OrderFulfillmentSagaStarted`
- **Purpose:** Initiate the saga orchestration

## Registration Required

To use the new handlers, register them in `AppServiceProvider`:

```php
$this->app->resolving(CommandBus::class, function (CommandBus $bus, $app) {
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

## Event Flow After Handler Execution

```
Handler executes
    ↓
Event created
    ↓
Event stored in EventStore
    ↓
Event dispatched to Laravel dispatcher
    ↓
Event listeners triggered
    ↓
Read models updated
    ↓
Queue jobs queued
    ↓
Saga state updated
```

## Database Tables Involved

- **stored_events** - All domain events
- **inventory_reservations** - Inventory reservation records
- **shipments** - Shipment records
- **inventories** - Inventory levels

## Error Handling

Handlers include try-catch blocks:

```php
try {
    $result = $this->service->doSomething();
    
    if ($result['success']) {
        $event = new SuccessEvent(...);
    } else {
        $event = new FailureEvent(...);
    }
    
    $this->eventStore->store($event);
    $this->events->dispatch($event);
} catch (Throwable $e) {
    $event = new FailureEvent(...);
    $this->eventStore->store($event);
    $this->events->dispatch($event);
    throw $e;
}
```

## Testing Handlers

```php
// In test
$commandBus = app(CommandBus::class);

$command = new ReserveInventory(
    orderUuid: 'order-123',
    sagaUuid: 'saga-123',
    medications: [['medication_id' => 1, 'quantity' => 30]],
);

$commandBus->dispatch($command);

// Assert event was stored
$this->assertDatabaseHas('stored_events', [
    'event_type' => 'InventoryReserved',
]);

// Assert reservation was created
$this->assertDatabaseHas('inventory_reservations', [
    'status' => 'reserved',
]);
```

## Summary

**Handlers are the bridge between:**
- Commands (write requests)
- Business logic (services)
- Domain events (state changes)
- Event store (persistence)
- Event listeners (reactions)

**They implement CQRS by:**
- Separating command handling from queries
- Using event sourcing for persistence
- Triggering side effects via events
- Maintaining consistency boundaries

**Key files:**
- `app/Application/Commands/CommandBus.php` - Bus implementation
- `app/Providers/AppServiceProvider.php` - Handler registration
- `app/Application/Order/Handlers/*` - Handler implementations
- `app/Services/*Service.php` - Business logic services

See `HOW_HANDLERS_ARE_USED.md` for detailed explanation.
See `USING_NEW_HANDLERS_EXAMPLE.md` for practical examples.
