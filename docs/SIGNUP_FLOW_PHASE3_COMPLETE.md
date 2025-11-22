# Signup Flow - Phase 3 Complete ✅

## Overview
Phase 3 of the multi-step signup flow implementation is complete. All 8 commands and 8 command handlers have been created following the existing CQRS patterns, and the SignupAggregate has been enhanced with event stream reconstruction.

## Files Created

### Commands (8 total)
Located in `app/Application/Signup/Commands/`:

1. **StartSignup** - Initiates a new signup process
   - Parameters: signupId, userId, signupPath, metadata
   - Paths: medication_first, condition_first, plan_first

2. **SelectMedication** - Records medication selection
   - Parameters: signupId, medicationId, metadata

3. **SelectCondition** - Records condition selection
   - Parameters: signupId, conditionId, metadata

4. **SelectPlan** - Records plan selection
   - Parameters: signupId, planId, metadata

5. **CompleteQuestionnaire** - Records questionnaire responses
   - Parameters: signupId, responses (array), metadata

6. **ProcessPayment** - Records payment processing
   - Parameters: signupId, paymentId, amount, status, metadata

7. **CreateSubscription** - Creates subscription after payment
   - Parameters: signupId, subscriptionId, userId, planId, medicationId, conditionId, metadata

8. **FailSignup** - Records signup failure
   - Parameters: signupId, reason, message, metadata

### Command Handlers (8 total)
Located in `app/Application/Signup/Handlers/`:

1. **StartSignupHandler** - Creates new signup aggregate
2. **SelectMedicationHandler** - Loads aggregate and records medication selection
3. **SelectConditionHandler** - Loads aggregate and records condition selection
4. **SelectPlanHandler** - Loads aggregate and records plan selection
5. **CompleteQuestionnaireHandler** - Loads aggregate and records questionnaire
6. **ProcessPaymentHandler** - Loads aggregate and records payment
7. **CreateSubscriptionHandler** - Loads aggregate and creates subscription
8. **FailSignupHandler** - Loads aggregate and records failure

### SignupAggregate Enhancements

**New Methods:**
- `fromEventStream(string $signupId): self` - Reconstructs aggregate from event history
- `aggregateType(): string` - Returns 'signup' identifier

**Updated Methods:**
- `startSignup()` - Now accepts payload array instead of individual parameters
- `createSubscription()` - Now accepts all required parameters explicitly

## Command Flow

```
Controller/Request
    ↓
Create Command (e.g., SelectMedication)
    ↓
CommandBus::dispatch($command)
    ↓
CommandBus finds handler (e.g., SelectMedicationHandler)
    ↓
Handler::handle($command)
    ↓
Load aggregate from event stream (if not new)
    ↓
Call aggregate method (e.g., selectMedication())
    ↓
Aggregate records event (e.g., MedicationSelected)
    ↓
Handler stores event in EventStore
    ↓
Handler dispatches event to listeners
    ↓
Event listeners update read models
    ↓
Response returned to client
```

## Handler Pattern

All handlers follow the same pattern:

```php
class XxxHandler implements CommandHandler
{
    public function __construct(
        private EventStoreContract $eventStore,
        private Dispatcher $events,
    ) {
    }

    public function handle(Command $command): void
    {
        // 1. Validate command type
        if (! $command instanceof XxxCommand) {
            throw new InvalidArgumentException(...);
        }

        // 2. Load or create aggregate
        $aggregate = SignupAggregate::fromEventStream($command->signupId);
        // OR for new aggregates:
        // $aggregate = SignupAggregate::startSignup(...);

        // 3. Call aggregate method
        $aggregate->methodName($command->param1, $command->param2);

        // 4. Store and dispatch events
        foreach ($aggregate->releaseEvents() as $event) {
            $this->eventStore->store($event);
            $this->events->dispatch($event);
        }
    }
}
```

## Registration in AppServiceProvider

All 8 handlers are registered in `app/Providers/AppServiceProvider.php`:

```php
$bus->register(StartSignup::class, $app->make(StartSignupHandler::class));
$bus->register(SelectMedication::class, $app->make(SelectMedicationHandler::class));
$bus->register(SelectCondition::class, $app->make(SelectConditionHandler::class));
$bus->register(SelectPlan::class, $app->make(SelectPlanHandler::class));
$bus->register(CompleteQuestionnaire::class, $app->make(CompleteQuestionnaireHandler::class));
$bus->register(ProcessPayment::class, $app->make(ProcessPaymentHandler::class));
$bus->register(CreateSubscription::class, $app->make(CreateSubscriptionHandler::class));
$bus->register(FailSignup::class, $app->make(FailSignupHandler::class));
```

## Event Stream Reconstruction

The `fromEventStream()` method:
1. Queries StoredEvent table for all events with matching signup_uuid
2. Filters by aggregate_type = 'signup'
3. Orders by id (chronological order)
4. Converts each StoredEvent to DomainEvent
5. Reconstructs aggregate state by applying all events

This enables:
- Loading signup state at any point in time
- Replaying events for debugging
- Rebuilding read models
- Audit trails

## Next Steps (Phase 4)

Phase 4 will create:
1. Signup controllers for each step
2. Routes for signup endpoints
3. Request validation classes
4. Response formatting

## Architecture Patterns Used

### CQRS (Command Query Responsibility Segregation)
- **Write Side**: Commands → Handlers → Aggregate → Events
- **Read Side**: Events → Listeners → Read Models

### Event Sourcing
- All state changes recorded as immutable events
- Aggregate reconstructed from event history
- Complete audit trail maintained

### Command Bus Pattern
- Centralized command routing
- Handler registration in service provider
- Type-safe command dispatch

