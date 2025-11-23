# Signup Flow Phase 4 - Quick Reference

## What Was Created

### 1 Controller + 9 Endpoints = Complete HTTP Layer

```
app/Http/Controllers/Signup/
└── SignupController.php (9 methods)
    ├── start()
    ├── selectMedication()
    ├── selectCondition()
    ├── selectPlan()
    ├── completeQuestionnaire()
    ├── processPayment()
    ├── createSubscription()
    ├── fail()
    └── status()
```

## Endpoints

| Method | Endpoint | Purpose |
|--------|----------|---------|
| POST | `/signup/start` | Start signup process |
| POST | `/signup/select-medication` | Select medication |
| POST | `/signup/select-condition` | Select condition |
| POST | `/signup/select-plan` | Select plan |
| POST | `/signup/complete-questionnaire` | Complete questionnaire |
| POST | `/signup/process-payment` | Process payment |
| POST | `/signup/create-subscription` | Create subscription |
| POST | `/signup/fail` | Fail signup |
| GET | `/signup/{signupId}/status` | Get signup status |

## API Usage

### Start Signup
```bash
POST /signup/start
Content-Type: application/json

{
  "signup_path": "medication_first"
}

Response:
{
  "success": true,
  "signup_id": "550e8400-e29b-41d4-a716-446655440000",
  "message": "Signup process started"
}
```

### Select Medication
```bash
POST /signup/select-medication
Content-Type: application/json

{
  "signup_id": "550e8400-e29b-41d4-a716-446655440000",
  "medication_id": "660e8400-e29b-41d4-a716-446655440000"
}

Response:
{
  "success": true,
  "message": "Medication selected"
}
```

### Complete Questionnaire
```bash
POST /signup/complete-questionnaire
Content-Type: application/json

{
  "signup_id": "550e8400-e29b-41d4-a716-446655440000",
  "responses": {
    "q1": "answer1",
    "q2": "answer2"
  }
}

Response:
{
  "success": true,
  "message": "Questionnaire completed"
}
```

### Process Payment
```bash
POST /signup/process-payment
Content-Type: application/json

{
  "signup_id": "550e8400-e29b-41d4-a716-446655440000",
  "payment_id": "pay-123",
  "amount": 99.99,
  "status": "success"
}

Response:
{
  "success": true,
  "message": "Payment processed"
}
```

### Create Subscription
```bash
POST /signup/create-subscription
Content-Type: application/json

{
  "signup_id": "550e8400-e29b-41d4-a716-446655440000",
  "subscription_id": "sub-123",
  "user_id": "user-456",
  "plan_id": "plan-789",
  "medication_id": "med-101",
  "condition_id": "cond-202"
}

Response:
{
  "success": true,
  "message": "Subscription created"
}
```

### Get Status
```bash
GET /signup/550e8400-e29b-41d4-a716-446655440000/status

Response:
{
  "success": true,
  "data": {
    "signup_uuid": "550e8400-e29b-41d4-a716-446655440000",
    "user_id": "user-456",
    "signup_path": "medication_first",
    "medication_id": "med-101",
    "condition_id": null,
    "plan_id": "plan-789",
    "questionnaire_responses": {"q1": "answer1"},
    "payment_id": "pay-123",
    "payment_amount": 99.99,
    "payment_status": "success",
    "subscription_id": "sub-123",
    "status": "completed",
    "failure_reason": null,
    "failure_message": null
  }
}
```

## Validation Rules

### All Endpoints
- All IDs must be valid UUIDs (except payment_id and user_id)
- All required fields must be present
- Enum values must match allowed values

### Signup Paths
- `medication_first` - Select medication → plan → questionnaire → payment
- `condition_first` - Select condition → plan → questionnaire → payment
- `plan_first` - Select plan → payment → medication/condition selection

### Payment Status
- `success` - Payment successful
- `pending` - Payment pending
- `failed` - Payment failed

### Failure Reasons
- `validation_error` - Validation failed
- `payment_failed` - Payment processing failed
- `system_error` - System error occurred

## Files Modified

- `routes/web.php` - Added signup route group

## Files Created

- `app/Http/Controllers/Signup/SignupController.php` - Main controller

## Architecture Flow

```
Request
  ↓
SignupController
  ↓
Validation
  ↓
Command Creation
  ↓
CommandBus::dispatch()
  ↓
Handler (Phase 3)
  ↓
Aggregate (Phase 1)
  ↓
Event (Phase 1)
  ↓
EventStore (Phase 2)
  ↓
Event Listeners (Phase 2)
  ↓
SignupReadModel (Phase 2)
  ↓
JSON Response
```

## Testing

All endpoints are ready for testing. See Phase 5 for comprehensive test suite.

