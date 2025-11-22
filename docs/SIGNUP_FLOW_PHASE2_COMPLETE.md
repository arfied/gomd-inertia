# Signup Flow - Phase 2 Complete ✅

## Overview
Phase 2 of the multi-step signup flow implementation is complete. The read model, database migration, and all 8 event listeners have been created following the existing CQRS patterns.

## Files Created

### Read Model
- **`app/Models/SignupReadModel.php`** - Eloquent model for signup queries
  - Tracks all signup state: path, medication, condition, plan, questionnaire responses, payment, subscription
  - Provides query scopes: forUser(), withStatus(), completed(), pending(), failed(), byPath(), withPlan(), withMedication(), withCondition()
  - Casts questionnaire_responses as JSON

### Database Migration
- **`database/migrations/2025_11_22_000002_create_signup_read_model_table.php`**
  - Creates `signup_read_model` table with all necessary columns
  - Includes indexes for common queries: user_id, signup_path, status, plan_id, medication_id, condition_id, created_at, updated_at
  - Supports all signup paths and tracks payment and subscription data

### Event Listeners (8 total)
1. **`ProjectSignupStarted`** - Creates new signup record
2. **`ProjectMedicationSelected`** - Updates medication_id
3. **`ProjectConditionSelected`** - Updates condition_id
4. **`ProjectPlanSelected`** - Updates plan_id
5. **`ProjectQuestionnaireCompleted`** - Updates questionnaire_responses (JSON)
6. **`ProjectPaymentProcessed`** - Updates payment_id, payment_amount, payment_status
7. **`ProjectSubscriptionCreated`** - Updates subscription_id and sets status to 'completed'
8. **`ProjectSignupFailed`** - Sets status to 'failed' and records failure_reason and failure_message

### Configuration Updates
- **`config/projection_replay.php`**
  - Added 8 event type mappings for signup events
  - Added 'signup' projection definition that includes all 8 event types

## Database Schema

### signup_read_model table
```
- id (primary key)
- signup_uuid (unique)
- user_id (indexed)
- signup_path (indexed) - medication_first, condition_first, plan_first
- medication_id (indexed, nullable)
- condition_id (indexed, nullable)
- plan_id (indexed, nullable)
- questionnaire_responses (JSON, nullable)
- payment_id (nullable)
- payment_amount (decimal, nullable)
- payment_status (nullable) - success, pending, failed
- subscription_id (nullable)
- status (indexed) - pending, completed, failed
- failure_reason (nullable) - validation_error, payment_failed, system_error
- failure_message (text, nullable)
- created_at (indexed)
- updated_at (indexed)
```

## Event Flow

```
Domain Event Recorded
    ↓
Event stored in event_store table
    ↓
Laravel Event Dispatcher triggered
    ↓
Event Listener (ProjectXxx) called
    ↓
SignupReadModel updated via updateOrCreate() or update()
    ↓
Query available via SignupReadModel scopes
```

## Query Examples

```php
// Get all signups for a user
SignupReadModel::forUser($userId)->get();

// Get completed signups
SignupReadModel::completed()->get();

// Get pending signups
SignupReadModel::pending()->get();

// Get signups by path
SignupReadModel::byPath('medication_first')->get();

// Get signups with specific plan
SignupReadModel::withPlan($planId)->get();

// Get signups with specific medication
SignupReadModel::withMedication($medicationId)->get();

// Get signups with specific condition
SignupReadModel::withCondition($conditionId)->get();

// Get failed signups
SignupReadModel::failed()->get();
```

## Migration Status

✅ Migration ran successfully:
```
2025_11_22_000002_create_signup_read_model_table  136.28ms DONE
```

## Next Steps (Phase 3)

Phase 3 will create:
1. Event handlers (command handlers) for each signup step
2. Commands for each step (StartSignup, SelectMedication, SelectCondition, etc.)
3. Command bus registration in AppServiceProvider

## Architecture Patterns Used

### CQRS
- **Write Side**: SignupAggregate (Phase 1) handles commands and records events
- **Read Side**: SignupReadModel (Phase 2) materialized from events via listeners
- **Event Handlers**: Listeners project events to read model

### Event Sourcing
- All state changes recorded as immutable events
- Read model reconstructed from event history
- Supports event replay and projection rebuilding

### Listener Pattern
- Laravel auto-discovers listeners in `app/Listeners/`
- Type-hinted event parameter enables automatic registration
- Listeners update read models when events are dispatched

