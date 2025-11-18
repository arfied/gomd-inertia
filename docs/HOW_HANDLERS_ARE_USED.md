# How Handlers in Application Folder Are Used

## Overview

Handlers in the `app/Application/Order/Handlers/` folder are used to process commands through a **Command Bus** pattern. They implement the CQRS (Command Query Responsibility Segregation) pattern.

## Architecture Flow

```
Controller/Job/Saga
    ↓
Creates Command
    ↓
Injects CommandBus
    ↓
commandBus->dispatch($command)
    ↓
CommandBus looks up handler
    ↓
Handler->handle($command)
    ↓
Handler creates/modifies aggregate
    ↓
Handler stores events in EventStore
    ↓
Handler dispatches events to listeners
```

## 1. Command Bus Registration

**Location:** `app/Providers/AppServiceProvider.php`

Handlers are registered in the service provider using the `resolving` callback:

```php
$this->app->resolving(CommandBus::class, function (CommandBus $bus, $app) {
    // Register ReserveInventory command
    $bus->register(
        ReserveInventory::class,
        $app->make(ReserveInventoryHandler::class)
    );

    // Register InitiateShipment command
    $bus->register(
        InitiateShipment::class,
        $app->make(InitiateShipmentHandler::class)
    );

    // ... other handlers
});
```

## 2. Command Bus Implementation

**Location:** `app/Application/Commands/CommandBus.php`

The CommandBus is a simple in-memory registry:

```php
class CommandBus
{
    private array $handlers = [];

    public function register(string $commandClass, CommandHandler $handler): void
    {
        $this->handlers[$commandClass] = $handler;
    }

    public function dispatch(Command $command): void
    {
        $commandClass = $command::class;

        if (! isset($this->handlers[$commandClass])) {
            throw new InvalidArgumentException("No handler registered for command {$commandClass}");
        }

        $this->handlers[$commandClass]->handle($command);
    }
}
```

## 3. Handler Interface

**Location:** `app/Application/Commands/CommandHandler.php`

All handlers implement this interface:

```php
interface CommandHandler
{
    public function handle(Command $command): void;
}
```

## 4. Usage in Controllers

**Example:** `app/Http/Controllers/PatientDemographicsController.php`

```php
public function update(Request $request, CommandBus $commandBus): JsonResponse
{
    // 1. Create command
    $command = new UpdatePatientDemographics(
        patientUuid: $enrollment->patient_uuid,
        userId: $authUser->id,
        demographics: $data,
        metadata: ['source' => 'manual'],
    );

    // 2. Dispatch command via CommandBus
    $commandBus->dispatch($command);

    // 3. Return response
    return $this->formatDemographicsResponse($user, $enrollment);
}
```

## 5. Handler Implementation Pattern

**Example:** `app/Application/Order/Handlers/ReserveInventoryHandler.php`

```php
class ReserveInventoryHandler implements CommandHandler
{
    public function __construct(
        private EventStoreContract $eventStore,
        private Dispatcher $events,
        private InventoryReservationService $inventoryService,
    ) {
    }

    public function handle(Command $command): void
    {
        // 1. Type check
        if (! $command instanceof ReserveInventory) {
            throw new InvalidArgumentException('...');
        }

        // 2. Call business logic service
        $result = $this->inventoryService->reserve(
            medications: $command->medications,
            warehouseId: $command->warehouseId,
        );

        // 3. Create event based on result
        if ($result['success']) {
            $event = new InventoryReserved($command->orderUuid, $payload, ...);
        } else {
            $event = new InventoryReservationFailed($command->orderUuid, $payload, ...);
        }

        // 4. Store event in EventStore
        $this->eventStore->store($event);

        // 5. Dispatch event to listeners
        $this->events->dispatch($event);
    }
}
```

## 6. Usage in Jobs/Queues

Handlers can also be called from queue jobs:

```php
class ReserveInventoryJob implements ShouldQueue
{
    public function handle(CommandBus $commandBus)
    {
        $command = new ReserveInventory(
            orderUuid: $this->orderUuid,
            sagaUuid: $this->sagaUuid,
            medications: $this->medications,
        );

        $commandBus->dispatch($command);
    }
}
```

## 7. Usage in Sagas

Handlers are called from saga orchestrators:

```php
class OrderFulfillmentSaga
{
    public function reserveInventory(CommandBus $commandBus)
    {
        $command = new ReserveInventory(
            orderUuid: $this->orderUuid,
            sagaUuid: $this->sagaUuid,
            medications: $this->medications,
        );

        $commandBus->dispatch($command);
    }
}
```

## 8. Event Flow After Handler Execution

After a handler stores and dispatches an event:

1. **Event Stored** - Event persisted in `stored_events` table
2. **Event Dispatched** - Laravel's event dispatcher triggers listeners
3. **Listeners Execute** - Event listeners in `app/Listeners/` respond
4. **Projections Updated** - Read models updated
5. **Queue Jobs Triggered** - Async jobs queued if needed

## 9. Dependency Injection

Handlers use constructor injection for dependencies:

```php
public function __construct(
    private EventStoreContract $eventStore,      // Event storage
    private Dispatcher $events,                   // Event dispatcher
    private InventoryReservationService $service, // Business logic
) {
}
```

## 10. Error Handling

Handlers include try-catch for error handling:

```php
try {
    $result = $this->inventoryService->reserve(...);
    
    if ($result['success']) {
        $event = new InventoryReserved(...);
    } else {
        $event = new InventoryReservationFailed(...);
    }
    
    $this->eventStore->store($event);
    $this->events->dispatch($event);
} catch (Throwable $e) {
    $event = new InventoryReservationFailed(...);
    $this->eventStore->store($event);
    $this->events->dispatch($event);
    throw $e;
}
```

## Summary

**Handlers are used to:**
1. ✅ Process commands from controllers, jobs, or sagas
2. ✅ Call business logic services
3. ✅ Create domain events
4. ✅ Store events in EventStore
5. ✅ Dispatch events to listeners
6. ✅ Implement CQRS write model
7. ✅ Maintain event sourcing

**Key Points:**
- Registered in `AppServiceProvider`
- Dispatched via `CommandBus`
- Implement `CommandHandler` interface
- Inject dependencies via constructor
- Store and dispatch events
- Handle errors gracefully
