# Questionnaire Integration - Code Examples

## 1. Data Migration

### Migration File
```php
// database/migrations/2025_11_24_migrate_questions_to_questionnaire.php
public function up(): void
{
    $questions = Question::with('options')->get();
    
    $formatted = $questions->map(function($q) {
        return [
            'id' => "q{$q->id}",
            'text' => $q->question,
            'type' => $q->type,
            'required' => $q->required,
            'options' => $q->options->pluck('option_value')->toArray(),
            'section' => $this->mapServiceToSection($q->service_id),
            'order' => $q->order,
        ];
    })->toArray();

    QuestionnaireReadModel::create([
        'questionnaire_uuid' => Str::uuid(),
        'title' => 'Comprehensive Health Screening',
        'description' => 'Initial health assessment',
        'questions' => $formatted,
        'status' => 'active',
    ]);
}

private function mapServiceToSection($serviceId): string
{
    $mapping = [
        1 => 'cardiovascular',
        2 => 'neurological',
        3 => 'gastrointestinal',
        // ... more mappings
    ];
    return $mapping[$serviceId] ?? 'general';
}
```

## 2. Domain Events

### QuestionnaireCreated Event
```php
// app/Domain/Clinical/Events/QuestionnaireCreated.php
namespace App\Domain\Clinical\Events;

class QuestionnaireCreated extends DomainEvent
{
    public function __construct(
        public string $questionnaireUuid,
        public string $title,
        public string $description,
        public array $questions,
        public string $createdBy,
    ) {}

    public static function fromStoredEventData(array $data): self
    {
        return new self(
            questionnaireUuid: $data['questionnaire_uuid'],
            title: $data['title'],
            description: $data['description'],
            questions: $data['questions'],
            createdBy: $data['created_by'],
        );
    }
}
```

### QuestionnaireResponseSubmitted Event
```php
// app/Domain/Clinical/Events/QuestionnaireResponseSubmitted.php
namespace App\Domain\Clinical\Events;

class QuestionnaireResponseSubmitted extends DomainEvent
{
    public function __construct(
        public string $questionnaireUuid,
        public string $patientId,
        public array $responses,
        public \DateTime $submittedAt,
    ) {}

    public static function fromStoredEventData(array $data): self
    {
        return new self(
            questionnaireUuid: $data['questionnaire_uuid'],
            patientId: $data['patient_id'],
            responses: $data['responses'],
            submittedAt: new \DateTime($data['submitted_at']),
        );
    }
}
```

## 3. Event Handler

### Update Read Model
```php
// app/Domain/Clinical/Handlers/QuestionnaireCreatedHandler.php
namespace App\Domain\Clinical\Handlers;

class QuestionnaireCreatedHandler
{
    public function handle(QuestionnaireCreated $event): void
    {
        QuestionnaireReadModel::create([
            'questionnaire_uuid' => $event->questionnaireUuid,
            'title' => $event->title,
            'description' => $event->description,
            'questions' => $event->questions,
            'status' => 'active',
            'created_by' => $event->createdBy,
        ]);
    }
}
```

## 4. API Endpoint Enhancement

### Submit Questionnaire Response
```php
// app/Http/Controllers/Api/QuestionnaireController.php
public function submit(Request $request, string $id): JsonResponse
{
    $validated = $request->validate([
        'responses' => 'required|array',
    ]);

    $questionnaire = QuestionnaireReadModel::where(
        'questionnaire_uuid', 
        $id
    )->firstOrFail();

    // Validate responses
    $this->validateResponses($validated['responses'], $questionnaire->questions);

    // Dispatch event
    event(new QuestionnaireResponseSubmitted(
        questionnaireUuid: $id,
        patientId: auth()->id(),
        responses: $validated['responses'],
        submittedAt: now(),
    ));

    return response()->json([
        'success' => true,
        'message' => 'Questionnaire submitted successfully',
    ]);
}

private function validateResponses(array $responses, array $questions): void
{
    foreach ($questions as $question) {
        if ($question['required'] && !isset($responses[$question['id']])) {
            throw new \InvalidArgumentException(
                "Question {$question['id']} is required"
            );
        }
    }
}
```

## 5. Vue Component

### DynamicQuestionnaireForm.vue
```vue
<template>
  <form @submit.prevent="handleSubmit" class="space-y-6">
    <div v-for="question in visibleQuestions" :key="question.id">
      <QuestionField
        :question="question"
        :value="responses[question.id]"
        :errors="errors[question.id]"
        @update="responses[question.id] = $event"
      />
    </div>
    <button type="submit" :disabled="loading" class="btn btn-primary">
      {{ loading ? 'Submitting...' : 'Submit' }}
    </button>
  </form>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import axios from 'axios'
import QuestionField from './QuestionField.vue'

interface Question {
  id: string
  text: string
  type: string
  required: boolean
  options?: string[]
  conditional?: { parentQuestionId: string; parentAnswerValue: string }
}

const props = defineProps<{
  questions: Question[]
  questionnaireId: string
}>()

const emit = defineEmits<{
  submit: [responses: Record<string, any>]
  error: [error: string]
}>()

const responses = ref<Record<string, any>>({})
const errors = ref<Record<string, string>>({})
const loading = ref(false)

const visibleQuestions = computed(() => {
  return props.questions.filter(q => {
    if (!q.conditional) return true
    const parentAnswer = responses.value[q.conditional.parentQuestionId]
    return parentAnswer === q.conditional.parentAnswerValue
  })
})

async function handleSubmit() {
  loading.value = true
  try {
    await axios.post(
      `/api/questionnaires/${props.questionnaireId}/submit`,
      { responses: responses.value }
    )
    emit('submit', responses.value)
  } catch (error: any) {
    errors.value = error.response?.data?.errors || {}
    emit('error', error.response?.data?.message || 'Submission failed')
  } finally {
    loading.value = false
  }
}
</script>
```

## 6. Integration in Signup Flow

### Updated SignupQuestionnaireStep.vue
```vue
<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'
import DynamicQuestionnaireForm from './DynamicQuestionnaireForm.vue'

const questions = ref([])
const loading = ref(true)
const questionnaireId = ref('')

onMounted(async () => {
  try {
    const response = await axios.get('/api/questionnaires')
    questions.value = response.data.data
    questionnaireId.value = response.data.questionnaire_uuid
  } catch (error) {
    console.error('Failed to load questionnaire:', error)
  } finally {
    loading.value = false
  }
})

function handleSubmit(responses: Record<string, any>) {
  // Update signup store with responses
  signupStore.setQuestionnaireResponses(responses)
  // Navigate to next step
  router.push('/signup/payment')
}
</script>

<template>
  <div v-if="loading" class="text-center">Loading questionnaire...</div>
  <DynamicQuestionnaireForm
    v-else
    :questions="questions"
    :questionnaire-id="questionnaireId"
    @submit="handleSubmit"
  />
</template>
```

## 7. Testing Example

### Feature Test
```php
// tests/Feature/QuestionnaireSubmissionTest.php
it('submits questionnaire responses', function () {
    $questionnaire = QuestionnaireReadModel::create([
        'questionnaire_uuid' => 'uuid-123',
        'title' => 'Test Questionnaire',
        'questions' => [
            ['id' => 'q1', 'text' => 'Question 1', 'type' => 'text', 'required' => true],
        ],
        'status' => 'active',
    ]);

    $response = $this->postJson('/api/questionnaires/uuid-123/submit', [
        'responses' => ['q1' => 'Answer to question 1'],
    ]);

    $response->assertStatus(200)
        ->assertJson(['success' => true]);

    $this->assertDatabaseHas('questionnaire_responses', [
        'questionnaire_uuid' => 'uuid-123',
        'responses' => json_encode(['q1' => 'Answer to question 1']),
    ]);
});
```

## Key Patterns

1. **Event Sourcing**: All changes recorded as events
2. **Read Models**: Denormalized data for fast queries
3. **Domain Events**: `fromStoredEventData()` for rehydration
4. **API Design**: RESTful endpoints with JSON
5. **Vue Components**: Reusable, composable, testable
6. **Validation**: Both client and server-side

