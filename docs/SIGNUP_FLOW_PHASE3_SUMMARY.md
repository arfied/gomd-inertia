# Phase 3 Summary - Commands & Handlers

## 📊 Progress: 3/8 Phases Complete (37.5%)

## ✅ What Was Delivered

### 8 Commands (Write Model DTOs)
Located in `app/Application/Signup/Commands/`:

| Command | Purpose | Parameters |
|---------|---------|------------|
| StartSignup | Initiate signup | signupId, userId, signupPath |
| SelectMedication | Record medication | signupId, medicationId |
| SelectCondition | Record condition | signupId, conditionId |
| SelectPlan | Record plan | signupId, planId |
| CompleteQuestionnaire | Record responses | signupId, responses (array) |
| ProcessPayment | Record payment | signupId, paymentId, amount, status |
| CreateSubscription | Create subscription | signupId, subscriptionId, userId, planId, medicationId, conditionId |
| FailSignup | Record failure | signupId, reason, message |

### 8 Command Handlers (Write Model Processors)
Located in `app/Application/Signup/Handlers/`:

Each handler:
1. Validates command type
2. Loads or creates aggregate
3. Calls aggregate method
4. Stores events in EventStore
5. Dispatches events to listeners

### SignupAggregate Enhancements

**New Methods:**
- `fromEventStream(string $signupId): self` - Reconstructs aggregate from event history
- `aggregateType(): string` - Returns 'signup' identifier

**Updated Methods:**
- `startSignup(string $signupId, array $payload, array $metadata = []): self`
- `createSubscription(string $subscriptionId, string $userId, string $planId, ?string $medicationId, ?string $conditionId): void`

### AppServiceProvider Registration

All 8 handlers registered in `app/Providers/AppServiceProvider.php`:
- Added 16 use statements (8 commands + 8 handlers)
- Registered all handlers in CommandBus resolving callback

## 📁 Files Created/Modified (11 total)

### Created (10 files)
```
app/Application/Signup/Commands/
├── StartSignup.php
├── SelectMedication.php
├── SelectCondition.php
├── SelectPlan.php
├── CompleteQuestionnaire.php
├── ProcessPayment.php
├── CreateSubscription.php
└── FailSignup.php

app/Application/Signup/Handlers/
├── StartSignupHandler.php
├── SelectMedicationHandler.php
├── SelectConditionHandler.php
├── SelectPlanHandler.php
├── CompleteQuestionnaireHandler.php
├── ProcessPaymentHandler.php
├── CreateSubscriptionHandler.php
└── FailSignupHandler.php
```

### Modified (1 file)
- `app/Providers/AppServiceProvider.php` - Added imports and handler registrations
- `app/Domain/Signup/SignupAggregate.php` - Added fromEventStream() and aggregateType()

## 🔄 Complete CQRS Flow

```
Request
  ↓
Create Command (DTO)
  ↓
CommandBus::dispatch($command)
  ↓
CommandBus finds handler via registration
  ↓
Handler::handle($command)
  ├─ Validate command type
  ├─ Load aggregate from event stream
  ├─ Call aggregate method
  └─ Aggregate records event
  ↓
Handler stores event in EventStore
  ↓
Handler dispatches event to Laravel Event Dispatcher
  ↓
Event Listeners (from Phase 2)
  ├─ ProjectSignupStarted
  ├─ ProjectMedicationSelected
  ├─ ProjectConditionSelected
  ├─ ProjectPlanSelected
  ├─ ProjectQuestionnaireCompleted
  ├─ ProjectPaymentProcessed
  ├─ ProjectSubscriptionCreated
  └─ ProjectSignupFailed
  ↓
SignupReadModel updated
  ↓
Response returned to client
```

## 🎯 Key Design Decisions

1. **Command Bus Pattern**: Centralized routing via AppServiceProvider
2. **Event Stream Reconstruction**: Load aggregate state from event history
3. **Immutable Commands**: Commands are DTOs with no behavior
4. **Handler Consistency**: All handlers follow same pattern
5. **Metadata Support**: All commands support optional metadata for audit trails

## 📝 Usage Example

```php
use App\Application\Signup\Commands\SelectMedication;
use App\Application\Commands\CommandBus;

// In controller
public function selectMedication(Request $request, CommandBus $bus)
{
    $command = new SelectMedication(
        signupId: $request->input('signup_id'),
        medicationId: $request->input('medication_id'),
    );

    $bus->dispatch($command);

    return response()->json(['success' => true]);
}
```

## 🚀 Next Phase (Phase 4)

Phase 4 will create:
1. **SignupController** - HTTP endpoints for each step
2. **Routes** - RESTful signup endpoints
3. **Request Validation** - Form request classes
4. **Response Formatting** - JSON responses with progress tracking

## ✨ Architecture Highlights

- **Event Sourcing**: Complete audit trail of all signup actions
- **CQRS**: Separation of write (commands) and read (queries) models
- **Aggregate Pattern**: Domain logic encapsulated in SignupAggregate
- **Event-Driven**: Listeners react to events for side effects
- **Type-Safe**: Commands are strongly typed DTOs
- **Testable**: Each handler can be tested independently

