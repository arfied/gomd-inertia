# Signup Flow Implementation Patterns

## Reusable Patterns from Clinical/Compliance Implementation

### 1. Event Sourcing Pattern for Signup

**Events to Create**:
```php
// app/Domain/Signup/Events/
SignupStarted
MedicationSelected
ConditionSelected
PlanSelected
QuestionnaireCompleted
PaymentProcessed
SubscriptionCreated
SignupFailed
```

**Aggregate Pattern**:
```php
class SignupAggregate extends AggregateRoot
{
    public function __construct(
        private string $signupId,
        private string $userId,
        private ?string $medicationId = null,
        private ?string $conditionId = null,
        private ?string $planId = null,
        private array $questionnaireResponses = [],
        private string $status = 'pending'
    ) {}
    
    public function startSignup(string $userId): void
    {
        $this->recordEvent(new SignupStarted($this->signupId, $userId));
    }
    
    public function selectMedication(string $medicationId): void
    {
        $this->recordEvent(new MedicationSelected($this->signupId, $medicationId));
    }
    
    // ... other methods
}
```

### 2. Read Model Pattern for Signup State

**SignupReadModel**:
```php
class SignupReadModel extends Model
{
    protected $table = 'signup_read_models';
    
    protected $fillable = [
        'signup_uuid',
        'user_id',
        'medication_id',
        'condition_id',
        'plan_id',
        'questionnaire_responses',
        'status',
        'current_step',
        'created_at',
        'updated_at',
    ];
    
    protected $casts = [
        'questionnaire_responses' => 'json',
    ];
}
```

### 3. Controller Pattern for Multi-Step Signup

**Step 1 - Select Medication/Condition**:
```php
public function selectMedicationStep(Request $request): Response
{
    $medications = MedicationReadModel::active()->get();
    $conditions = ConditionReadModel::active()->get();
    
    return Inertia::render('signup/SelectMedicationOrCondition', [
        'medications' => $medications,
        'conditions' => $conditions,
        'signupData' => $request->session()->get('signup_data', []),
    ]);
}

public function storeMedicationSelection(Request $request, CommandBus $commandBus): RedirectResponse
{
    $data = $request->validate([
        'medication_id' => 'required|string',
        'signup_path' => 'required|in:medication_first,condition_first,plan_first',
    ]);
    
    $signupId = (string) Str::uuid();
    $command = new StartSignup(
        signupId: $signupId,
        userId: $request->user()->id,
        medicationId: $data['medication_id'],
        signupPath: $data['signup_path'],
    );
    
    $commandBus->dispatch($command);
    
    $request->session()->put('signup_data', [
        'signup_id' => $signupId,
        'medication_id' => $data['medication_id'],
        'step' => 'select_plan',
    ]);
    
    return redirect()->route('signup.select-plan');
}
```

### 4. Vue Component Pattern for Multi-Step Form

**Key State Management**:
```typescript
interface SignupState {
    signupId: string
    medicationId?: string
    conditionId?: string
    planId?: string
    questionnaireResponses: Record<string, any>
    currentStep: number
    totalSteps: number
    loading: boolean
    error: string | null
}

const state = ref<SignupState>({
    signupId: props.signupData?.signup_id || '',
    medicationId: props.signupData?.medication_id,
    conditionId: props.signupData?.condition_id,
    planId: props.signupData?.plan_id,
    questionnaireResponses: {},
    currentStep: props.currentStep || 1,
    totalSteps: 4,
    loading: false,
    error: null,
})

const canProceed = computed(() => {
    switch (state.value.currentStep) {
        case 1: return !!state.value.medicationId || !!state.value.conditionId
        case 2: return !!state.value.planId
        case 3: return Object.keys(state.value.questionnaireResponses).length > 0
        case 4: return true
        default: return false
    }
})

const canGoBack = computed(() => state.value.currentStep > 1)
```

**Step Navigation**:
```typescript
async function nextStep() {
    if (!canProceed.value) return
    
    state.value.loading = true
    state.value.error = null
    
    try {
        const response = await fetch(`/signup/step/${state.value.currentStep}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify(getStepData()),
        })
        
        if (!response.ok) throw new Error('Failed to save step')
        
        state.value.currentStep++
    } catch (error) {
        state.value.error = error instanceof Error ? error.message : 'An error occurred'
    } finally {
        state.value.loading = false
    }
}

function previousStep() {
    if (canGoBack.value) state.value.currentStep--
}

function getStepData() {
    switch (state.value.currentStep) {
        case 1: return { medication_id: state.value.medicationId, condition_id: state.value.conditionId }
        case 2: return { plan_id: state.value.planId }
        case 3: return { questionnaire_responses: state.value.questionnaireResponses }
        case 4: return {} // Payment handled separately
        default: return {}
    }
}
```

### 5. Conditional Questionnaire Loading

**Controller**:
```php
public function getQuestionnaire(Request $request): JsonResponse
{
    $medicationId = $request->query('medication_id');
    $conditionId = $request->query('condition_id');
    
    $questionnaire = QuestionnaireReadModel::query()
        ->when($medicationId, fn ($q) => $q->where('medication_id', $medicationId))
        ->when($conditionId, fn ($q) => $q->where('condition_id', $conditionId))
        ->first();
    
    if (!$questionnaire) {
        return response()->json(['error' => 'Questionnaire not found'], 404);
    }
    
    return response()->json(['questionnaire' => $questionnaire]);
}
```

**Vue Component**:
```typescript
async function loadQuestionnaire() {
    try {
        const params = new URLSearchParams()
        if (state.value.medicationId) params.set('medication_id', state.value.medicationId)
        if (state.value.conditionId) params.set('condition_id', state.value.conditionId)
        
        const response = await fetch(`/signup/questionnaire?${params}`, {
            credentials: 'same-origin',
        })
        
        if (!response.ok) throw new Error('Failed to load questionnaire')
        
        const data = await response.json()
        questionnaire.value = data.questionnaire
    } catch (error) {
        state.value.error = 'Failed to load questionnaire'
    }
}

watch(() => [state.value.medicationId, state.value.conditionId], () => {
    void loadQuestionnaire()
})
```

### 6. Testing Pattern for Multi-Step Signup

```php
it('completes signup flow with medication selection', function () {
    $user = User::factory()->create();
    $medication = MedicationReadModel::factory()->create();
    $plan = PlanReadModel::factory()->create();
    
    // Step 1: Select medication
    $response = $this->actingAs($user)->post('/signup/select-medication', [
        'medication_id' => $medication->id,
        'signup_path' => 'medication_first',
    ]);
    
    $response->assertRedirect('/signup/select-plan');
    
    // Step 2: Select plan
    $response = $this->actingAs($user)->post('/signup/select-plan', [
        'plan_id' => $plan->id,
    ]);
    
    $response->assertRedirect('/signup/questionnaire');
    
    // Step 3: Complete questionnaire
    $response = $this->actingAs($user)->post('/signup/questionnaire', [
        'responses' => ['q1' => 'answer1'],
    ]);
    
    $response->assertRedirect('/signup/payment');
});
```

### 7. Session Management for Signup State

**Middleware to Protect Signup Flow**:
```php
class ValidateSignupStep implements Middleware
{
    public function handle(Request $request, Closure $next)
    {
        $signupData = $request->session()->get('signup_data');
        $currentStep = $request->route('step');
        
        if (!$signupData || $signupData['step'] !== $currentStep) {
            return redirect()->route('signup.start');
        }
        
        return $next($request);
    }
}
```

## Key Differences from Clinical/Compliance

1. **Temporary State**: Signup data is temporary (session-based) until payment succeeds
2. **Multi-Step**: Requires step validation and navigation logic
3. **Conditional Logic**: Different paths based on user selection
4. **Payment Integration**: Final step involves payment processing
5. **Atomic Completion**: All steps must complete successfully before creating subscription

## Recommended Implementation Order

1. Create SignupAggregate and events
2. Create SignupReadModel and migrations
3. Create event handlers to update SignupReadModel
4. Create signup controllers for each step
5. Create multi-step Vue component
6. Integrate conditional questionnaire loading
7. Integrate payment provider
8. Write comprehensive tests
9. Add progress indicator and UX enhancements

