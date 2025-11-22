# Signup Flow Phase 1 - Quick Reference

## What Was Created

### 1 Aggregate + 8 Events = Complete Domain Model

```
app/Domain/Signup/
├── SignupAggregate.php (143 lines)
└── Events/
    ├── SignupStarted.php
    ├── MedicationSelected.php
    ├── ConditionSelected.php
    ├── PlanSelected.php
    ├── QuestionnaireCompleted.php
    ├── PaymentProcessed.php
    ├── SubscriptionCreated.php
    └── SignupFailed.php
```

## Key Classes

### SignupAggregate
**Location:** `app/Domain/Signup/SignupAggregate.php`

**State Properties:**
- `signupId` - UUID for this signup session
- `userId` - User initiating signup
- `signupPath` - One of: medication_first, condition_first, plan_first
- `medicationId` - Selected medication (nullable)
- `conditionId` - Selected condition (nullable)
- `planId` - Selected plan (nullable)
- `questionnaireResponses` - JSON responses
- `status` - pending | completed | failed
- `paymentId` - Payment transaction ID
- `subscriptionId` - Created subscription ID

**Key Methods:**
```php
// Factory method
SignupAggregate::startSignup($signupId, $userId, $signupPath)

// Step methods
$aggregate->selectMedication($medicationId)
$aggregate->selectCondition($conditionId)
$aggregate->selectPlan($planId)
$aggregate->completeQuestionnaire($responses)
$aggregate->processPayment($paymentId, $amount, $status)
$aggregate->createSubscription($subscriptionId)
$aggregate->fail($reason, $message)
```

## Event Types

| Event | Tracks | Status |
|-------|--------|--------|
| SignupStarted | userId, signupPath | ✅ |
| MedicationSelected | medicationId | ✅ |
| ConditionSelected | conditionId | ✅ |
| PlanSelected | planId | ✅ |
| QuestionnaireCompleted | responses (JSON) | ✅ |
| PaymentProcessed | paymentId, amount, status | ✅ |
| SubscriptionCreated | subscriptionId, planId, medicationId, conditionId | ✅ |
| SignupFailed | reason, message | ✅ |

## Event Type Strings

For `config/projection_replay.php`:
```php
'signup.started' => App\Domain\Signup\Events\SignupStarted::class,
'signup.medication_selected' => App\Domain\Signup\Events\MedicationSelected::class,
'signup.condition_selected' => App\Domain\Signup\Events\ConditionSelected::class,
'signup.plan_selected' => App\Domain\Signup\Events\PlanSelected::class,
'signup.questionnaire_completed' => App\Domain\Signup\Events\QuestionnaireCompleted::class,
'signup.payment_processed' => App\Domain\Signup\Events\PaymentProcessed::class,
'signup.subscription_created' => App\Domain\Signup\Events\SubscriptionCreated::class,
'signup.failed' => App\Domain\Signup\Events\SignupFailed::class,
```

## Usage Example

```php
// Create new signup
$aggregate = SignupAggregate::startSignup(
    Str::uuid(),
    auth()->id(),
    'medication_first'
);

// Select medication
$aggregate->selectMedication('med-123');

// Select plan
$aggregate->selectPlan('plan-456');

// Complete questionnaire
$aggregate->completeQuestionnaire([
    'q1' => 'answer1',
    'q2' => 'answer2',
]);

// Process payment
$aggregate->processPayment('pay-789', 99.99, 'success');

// Create subscription
$aggregate->createSubscription('sub-000');

// Get recorded events
$events = $aggregate->releaseEvents();

// Store events
foreach ($events as $event) {
    $eventStore->store($event);
}
```

## Next Phase (Phase 2)

Will create:
1. `SignupReadModel` - Eloquent model for queries
2. Database migration - `signup_read_models` table
3. Event handlers - Project events to read model
4. Config updates - Add event type mappings

