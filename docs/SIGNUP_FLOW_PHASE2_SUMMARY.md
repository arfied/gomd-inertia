# Phase 2 Summary - Read Models & Migrations

## 📊 Progress: 2/8 Phases Complete (25%)

## ✅ What Was Delivered

### 1. SignupReadModel (1 file)
**Location:** `app/Models/SignupReadModel.php`

Eloquent model with 8 query scopes:
- `forUser($userId)` - Get signups for a specific user
- `withStatus($status)` - Filter by status
- `completed()` - Get completed signups
- `pending()` - Get pending signups
- `failed()` - Get failed signups
- `byPath($path)` - Filter by signup path
- `withPlan($planId)` - Filter by plan
- `withMedication($medicationId)` - Filter by medication
- `withCondition($conditionId)` - Filter by condition

### 2. Database Migration (1 file)
**Location:** `database/migrations/2025_11_22_000002_create_signup_read_model_table.php`

Created `signup_read_model` table with:
- **15 columns**: signup_uuid, user_id, signup_path, medication_id, condition_id, plan_id, questionnaire_responses, payment_id, payment_amount, payment_status, subscription_id, status, failure_reason, failure_message, timestamps
- **8 indexes**: user_id, signup_path, status, plan_id, medication_id, condition_id, created_at, updated_at
- **Status values**: pending, completed, failed
- **Payment status values**: success, pending, failed
- **Failure reasons**: validation_error, payment_failed, system_error

### 3. Event Listeners (8 files)
All located in `app/Listeners/`:

| Listener | Event | Action |
|----------|-------|--------|
| ProjectSignupStarted | SignupStarted | Create new signup record |
| ProjectMedicationSelected | MedicationSelected | Update medication_id |
| ProjectConditionSelected | ConditionSelected | Update condition_id |
| ProjectPlanSelected | PlanSelected | Update plan_id |
| ProjectQuestionnaireCompleted | QuestionnaireCompleted | Update questionnaire_responses (JSON) |
| ProjectPaymentProcessed | PaymentProcessed | Update payment fields |
| ProjectSubscriptionCreated | SubscriptionCreated | Update subscription_id, set status='completed' |
| ProjectSignupFailed | SignupFailed | Set status='failed', record failure details |

### 4. Configuration Updates
**File:** `config/projection_replay.php`

Added:
- 8 event type mappings (signup.started, signup.medication_selected, etc.)
- 'signup' projection definition with all 8 event types

## 🔄 Event Flow Architecture

```
SignupAggregate (Phase 1)
    ↓ recordThat(Event)
Domain Event
    ↓ releaseEvents()
Event Store
    ↓ store(event)
event_store table
    ↓ dispatch(event)
Laravel Event Dispatcher
    ↓ handle(event)
Event Listener (Phase 2)
    ↓ updateOrCreate() / update()
SignupReadModel
    ↓ query scopes
Application Layer
```

## 📁 Files Created (11 total)

```
app/Models/
└── SignupReadModel.php

app/Listeners/
├── ProjectSignupStarted.php
├── ProjectMedicationSelected.php
├── ProjectConditionSelected.php
├── ProjectPlanSelected.php
├── ProjectQuestionnaireCompleted.php
├── ProjectPaymentProcessed.php
├── ProjectSubscriptionCreated.php
└── ProjectSignupFailed.php

database/migrations/
└── 2025_11_22_000002_create_signup_read_model_table.php

config/
└── projection_replay.php (updated)
```

## ✅ Migration Status

Successfully ran:
```
2025_11_22_000002_create_signup_read_model_table  136.28ms DONE
```

## 🎯 What's Ready for Phase 3

The read model and event listeners are complete and ready for:
1. **Command Handlers** - Process signup steps
2. **Commands** - StartSignup, SelectMedication, SelectCondition, etc.
3. **Controllers** - HTTP endpoints for each step
4. **Frontend** - Vue component for multi-step form

## 📝 Key Design Decisions

1. **Listener Pattern**: Laravel auto-discovers listeners in `app/Listeners/` directory
2. **updateOrCreate()**: Used for SignupStarted to handle idempotency
3. **update()**: Used for subsequent events to update existing records
4. **JSON Storage**: Questionnaire responses stored as JSON for flexibility
5. **Comprehensive Indexing**: 8 indexes for optimal query performance
6. **Failure Tracking**: Separate columns for failure_reason and failure_message

## 🚀 Next Phase (Phase 3)

Phase 3 will create:
1. Command classes for each signup step
2. Command handlers to execute commands and record events
3. Command bus registration in AppServiceProvider
4. Validation logic for each step

