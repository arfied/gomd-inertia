# Signup Flow Phase 5 - Quick Reference

## What Was Created

### 1 Pinia Store + 1 Main Page + 8 Step Components = Complete Frontend

```
resources/js/
├── stores/
│   └── signupStore.ts (Pinia store with 8 actions)
├── pages/
│   └── Signup.vue (Main multi-step form)
└── components/Signup/
    ├── SignupPathSelector.vue
    ├── SignupMedicationStep.vue
    ├── SignupConditionStep.vue
    ├── SignupPlanStep.vue
    ├── SignupQuestionnaireStep.vue
    ├── SignupPaymentStep.vue
    ├── SignupSuccessStep.vue
    ├── SignupFailureStep.vue
    └── index.ts
```

## Signup Paths

| Path | Steps | Use Case |
|------|-------|----------|
| **Medication First** | Path → Medication → Plan → Questionnaire → Payment | Users know their medication |
| **Condition First** | Path → Condition → Plan → Questionnaire → Payment | Users know their condition |
| **Plan First** | Path → Plan → Payment | Users want to choose plan first |

## Pinia Store Actions

```typescript
// Initialize signup
await signupStore.startSignup('medication_first')

// Select options
await signupStore.selectMedication(medicationId)
await signupStore.selectCondition(conditionId)
await signupStore.selectPlan(planId)

// Complete steps
await signupStore.completeQuestionnaire(responses)
await signupStore.processPayment(paymentId, amount, status)
await signupStore.createSubscription(subscriptionId, userId)

// Handle failure
await signupStore.failSignup(reason, message)

// Reset
signupStore.reset()
```

## Component Props & Events

### SignupPathSelector
- Emits: path selection via store action
- Props: None (uses store)

### SignupMedicationStep
- Loads from: `/api/medications`
- Emits: medication selection via store action

### SignupConditionStep
- Loads from: `/api/conditions`
- Emits: condition selection via store action

### SignupPlanStep
- Loads from: `/api/plans`
- Emits: plan selection via store action

### SignupQuestionnaireStep
- Loads from: `/api/questionnaires?medication_id=X&condition_id=Y`
- Emits: questionnaire submission via store action

### SignupPaymentStep
- Payment methods: Credit Card, Bank Transfer
- Emits: payment processing via store action

### SignupSuccessStep
- Displays: Subscription details
- Actions: Go to Dashboard, Go to Home

### SignupFailureStep
- Displays: Error details and troubleshooting
- Actions: Contact Support, Try Again

## Store State

```typescript
const state = ref<SignupState>({
    signupId: null,
    userId: null,
    signupPath: null,
    medicationId: null,
    conditionId: null,
    planId: null,
    questionnaireResponses: {},
    paymentId: null,
    paymentAmount: null,
    paymentStatus: null,
    subscriptionId: null,
    status: 'pending',
    failureReason: null,
    failureMessage: null,
})

const loading = ref(false)
const error = ref<string | null>(null)
```

## Computed Properties

```typescript
const isStarted = computed(() => state.value.signupId !== null)
const isCompleted = computed(() => state.value.status === 'completed')
const isFailed = computed(() => state.value.status === 'failed')
```

## Routes

| Method | Route | Purpose |
|--------|-------|---------|
| GET | `/signup` | Render signup page |
| POST | `/signup/start` | Start signup |
| POST | `/signup/select-medication` | Select medication |
| POST | `/signup/select-condition` | Select condition |
| POST | `/signup/select-plan` | Select plan |
| POST | `/signup/complete-questionnaire` | Submit questionnaire |
| POST | `/signup/process-payment` | Process payment |
| POST | `/signup/create-subscription` | Create subscription |
| POST | `/signup/fail` | Record failure |
| GET | `/signup/{signupId}/status` | Get signup status |

## Usage Example

```vue
<script setup lang="ts">
import { useSignupStore } from '@/stores/signupStore'

const signupStore = useSignupStore()

async function handleSignup() {
    try {
        // Start signup
        await signupStore.startSignup('medication_first')
        
        // Select medication
        await signupStore.selectMedication('med-123')
        
        // Select plan
        await signupStore.selectPlan('plan-456')
        
        // Complete questionnaire
        await signupStore.completeQuestionnaire({ q1: 'answer1' })
        
        // Process payment
        await signupStore.processPayment('pay-789', 99.99, 'success')
        
        // Create subscription
        await signupStore.createSubscription('sub-101', 'user-202')
    } catch (error) {
        console.error('Signup failed:', error)
    }
}
</script>
```

## Features

✅ Multi-step form with 3 different paths
✅ Progress bar showing completion percentage
✅ Search and filter for medications/conditions
✅ Dynamic questionnaire loading
✅ Dual payment methods (Credit Card, Bank Transfer)
✅ Success and failure screens
✅ Error handling and troubleshooting
✅ Responsive mobile-friendly design
✅ Loading states and animations
✅ State persistence with Pinia

## Files Modified

- `routes/web.php` - Added GET /signup route

## Files Created

- `resources/js/stores/signupStore.ts`
- `resources/js/pages/Signup.vue`
- `resources/js/components/Signup/SignupPathSelector.vue`
- `resources/js/components/Signup/SignupMedicationStep.vue`
- `resources/js/components/Signup/SignupConditionStep.vue`
- `resources/js/components/Signup/SignupPlanStep.vue`
- `resources/js/components/Signup/SignupQuestionnaireStep.vue`
- `resources/js/components/Signup/SignupPaymentStep.vue`
- `resources/js/components/Signup/SignupSuccessStep.vue`
- `resources/js/components/Signup/SignupFailureStep.vue`
- `resources/js/components/Signup/index.ts`

## Testing

All components are ready for testing. See Phase 6 for comprehensive test suite.

