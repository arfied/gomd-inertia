# Signup Flow Phase 2 - Quick Reference

## What Was Created

### 1 Read Model + 1 Migration + 8 Listeners = Complete CQRS Read Side

```
app/Models/SignupReadModel.php (110 lines)
database/migrations/2025_11_22_000002_create_signup_read_model_table.php
app/Listeners/ProjectXxx.php (8 files)
config/projection_replay.php (updated)
```

## SignupReadModel Query Scopes

```php
// Get all signups for a user
SignupReadModel::forUser($userId)->get();

// Get signups by status
SignupReadModel::withStatus('pending')->get();
SignupReadModel::completed()->get();
SignupReadModel::pending()->get();
SignupReadModel::failed()->get();

// Get signups by path
SignupReadModel::byPath('medication_first')->get();

// Get signups by selection
SignupReadModel::withPlan($planId)->get();
SignupReadModel::withMedication($medicationId)->get();
SignupReadModel::withCondition($conditionId)->get();

// Combine scopes
SignupReadModel::forUser($userId)->completed()->get();
SignupReadModel::byPath('medication_first')->pending()->get();
```

## Database Schema

### signup_read_model table

| Column | Type | Nullable | Indexed |
|--------|------|----------|---------|
| id | bigint | No | Yes |
| signup_uuid | string | No | Yes (unique) |
| user_id | bigint | Yes | Yes |
| signup_path | string | No | Yes |
| medication_id | string | Yes | Yes |
| condition_id | string | Yes | Yes |
| plan_id | string | Yes | Yes |
| questionnaire_responses | json | Yes | No |
| payment_id | string | Yes | No |
| payment_amount | decimal | Yes | No |
| payment_status | string | Yes | No |
| subscription_id | string | Yes | No |
| status | string | No | Yes |
| failure_reason | string | Yes | No |
| failure_message | text | Yes | No |
| created_at | timestamp | No | Yes |
| updated_at | timestamp | No | Yes |

## Event Listeners

All listeners follow the same pattern:

```php
class ProjectXxx
{
    public function handle(XxxEvent $event): void
    {
        // Find or create signup record
        $signup = SignupReadModel::where('signup_uuid', $event->aggregateUuid)->first();
        
        if ($signup) {
            // Update with event data
            $signup->update([
                'field' => $event->property,
                'updated_at' => $event->occurredAt,
            ]);
        }
    }
}
```

## Event Type Mappings

In `config/projection_replay.php`:

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

## Projection Definition

In `config/projection_replay.php`:

```php
'signup' => [
    'signup.started',
    'signup.medication_selected',
    'signup.condition_selected',
    'signup.plan_selected',
    'signup.questionnaire_completed',
    'signup.payment_processed',
    'signup.subscription_created',
    'signup.failed',
],
```

## How It Works

1. **Aggregate records event** → `$aggregate->recordThat(new SignupStarted(...))`
2. **Event stored** → `$eventStore->store($event)`
3. **Event dispatched** → `$dispatcher->dispatch($event)`
4. **Listener called** → `ProjectSignupStarted::handle($event)`
5. **Read model updated** → `SignupReadModel::updateOrCreate(...)`
6. **Query available** → `SignupReadModel::forUser($userId)->get()`

## Migration Status

✅ Successfully created `signup_read_model` table

