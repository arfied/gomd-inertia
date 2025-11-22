# Signup Flow Phase 3 - Quick Reference

## What Was Created

### 8 Commands + 8 Handlers = Complete Write Side

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

## How to Use Commands

### Dispatch a Command

```php
use App\Application\Signup\Commands\SelectMedication;
use App\Application\Commands\CommandBus;

// In a controller or service
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

## Command Signatures

```php
// Start signup
new StartSignup(
    signupId: string,
    userId: string,
    signupPath: 'medication_first'|'condition_first'|'plan_first',
    metadata: array = [],
)

// Select medication
new SelectMedication(
    signupId: string,
    medicationId: string,
    metadata: array = [],
)

// Select condition
new SelectCondition(
    signupId: string,
    conditionId: string,
    metadata: array = [],
)

// Select plan
new SelectPlan(
    signupId: string,
    planId: string,
    metadata: array = [],
)

// Complete questionnaire
new CompleteQuestionnaire(
    signupId: string,
    responses: array,
    metadata: array = [],
)

// Process payment
new ProcessPayment(
    signupId: string,
    paymentId: string,
    amount: float,
    status: 'success'|'pending'|'failed',
    metadata: array = [],
)

// Create subscription
new CreateSubscription(
    signupId: string,
    subscriptionId: string,
    userId: string,
    planId: string,
    medicationId: ?string = null,
    conditionId: ?string = null,
    metadata: array = [],
)

// Fail signup
new FailSignup(
    signupId: string,
    reason: 'validation_error'|'payment_failed'|'system_error',
    message: string,
    metadata: array = [],
)
```

## Handler Registration

All handlers are auto-registered in `AppServiceProvider.php`:

```php
$bus->register(StartSignup::class, $app->make(StartSignupHandler::class));
$bus->register(SelectMedication::class, $app->make(SelectMedicationHandler::class));
// ... etc
```

## Event Stream Reconstruction

```php
use App\Domain\Signup\SignupAggregate;

// Load signup from event history
$signup = SignupAggregate::fromEventStream($signupId);

// Access state
echo $signup->status;           // 'pending', 'completed', 'failed'
echo $signup->medicationId;     // medication UUID or null
echo $signup->planId;           // plan UUID or null
echo $signup->subscriptionId;   // subscription UUID or null
```

## Flow Example: Medication First Path

```php
// 1. Start signup
$bus->dispatch(new StartSignup(
    signupId: 'signup-123',
    userId: 'user-456',
    signupPath: 'medication_first',
));

// 2. Select medication
$bus->dispatch(new SelectMedication(
    signupId: 'signup-123',
    medicationId: 'med-789',
));

// 3. Select plan
$bus->dispatch(new SelectPlan(
    signupId: 'signup-123',
    planId: 'plan-101',
));

// 4. Complete questionnaire
$bus->dispatch(new CompleteQuestionnaire(
    signupId: 'signup-123',
    responses: ['q1' => 'answer1', 'q2' => 'answer2'],
));

// 5. Process payment
$bus->dispatch(new ProcessPayment(
    signupId: 'signup-123',
    paymentId: 'pay-202',
    amount: 99.99,
    status: 'success',
));

// 6. Create subscription
$bus->dispatch(new CreateSubscription(
    signupId: 'signup-123',
    subscriptionId: 'sub-303',
    userId: 'user-456',
    planId: 'plan-101',
    medicationId: 'med-789',
));
```

## Files Modified

- `app/Providers/AppServiceProvider.php` - Added command imports and handler registrations
- `app/Domain/Signup/SignupAggregate.php` - Added fromEventStream() and aggregateType() methods

## Architecture

```
Request
  ↓
Command (DTO)
  ↓
CommandBus::dispatch()
  ↓
Handler (finds via registration)
  ↓
Aggregate (load or create)
  ↓
Aggregate method (records event)
  ↓
EventStore::store()
  ↓
Event Dispatcher
  ↓
Event Listeners (update read models)
  ↓
Response
```

