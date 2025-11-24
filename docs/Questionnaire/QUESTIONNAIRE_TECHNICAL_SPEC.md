# Questionnaire System - Technical Specification

## Data Structure

### Question JSON Format
```json
{
  "id": "q1",
  "text": "Have you been diagnosed with high blood pressure?",
  "type": "textarea",
  "required": true,
  "section": "cardiovascular",
  "order": 1,
  "options": null,
  "validation": {
    "minLength": 10,
    "maxLength": 1000
  },
  "conditional": {
    "parentQuestionId": null,
    "parentAnswerValue": null
  }
}
```

### Question Types
- **text**: Single-line text input
- **textarea**: Multi-line text input
- **select**: Dropdown with single selection
- **radio**: Radio buttons (single selection)
- **checkbox**: Checkboxes (multiple selection)
- **date**: Date picker
- **file**: File upload
- **number**: Numeric input

### Questionnaire JSON Structure
```json
{
  "questionnaire_uuid": "uuid-123",
  "title": "Comprehensive Health Screening",
  "description": "Initial health assessment",
  "questions": [
    { "id": "q1", "text": "...", "type": "textarea", ... },
    { "id": "q2", "text": "...", "type": "select", ... }
  ],
  "sections": {
    "cardiovascular": {
      "title": "Cardiovascular Assessment",
      "description": "Heart and blood pressure related questions",
      "questions": ["q1", "q2", "q3"]
    }
  },
  "logic": {
    "conditionalQuestions": [
      {
        "questionId": "q5",
        "condition": "q1 == 'yes'",
        "action": "show"
      }
    ]
  }
}
```

## Domain Events

### QuestionnaireCreated
```php
class QuestionnaireCreated extends DomainEvent {
    public function __construct(
        public string $questionnaireUuid,
        public string $title,
        public string $description,
        public array $questions,
        public string $createdBy,
    ) {}
}
```

### QuestionAdded
```php
class QuestionAdded extends DomainEvent {
    public function __construct(
        public string $questionnaireUuid,
        public array $question,
    ) {}
}
```

### QuestionnairePublished
```php
class QuestionnairePublished extends DomainEvent {
    public function __construct(
        public string $questionnaireUuid,
        public \DateTime $publishedAt,
    ) {}
}
```

### QuestionnaireResponseSubmitted
```php
class QuestionnaireResponseSubmitted extends DomainEvent {
    public function __construct(
        public string $questionnaireUuid,
        public string $patientId,
        public array $responses,
        public \DateTime $submittedAt,
    ) {}
}
```

## API Endpoints

### GET /api/questionnaires
Load questionnaire for signup flow
```
Query Parameters:
- medication_name: string (optional)
- condition_id: string (optional)

Response:
{
  "data": [
    {
      "id": "q1",
      "text": "Question text",
      "type": "textarea",
      "required": true,
      "options": null,
      "section": "cardiovascular"
    }
  ]
}
```

### POST /api/questionnaires/{id}/submit
Submit questionnaire responses
```
Request Body:
{
  "responses": {
    "q1": "Answer to question 1",
    "q2": ["Option 1", "Option 2"],
    "q3": "2024-01-15"
  }
}

Response:
{
  "success": true,
  "message": "Questionnaire submitted successfully",
  "submission_id": "uuid-123"
}
```

## Vue Component Structure

### DynamicQuestionnaireForm.vue
- Props: `questions`, `initialResponses`, `loading`
- Emits: `submit`, `update`, `error`
- Features:
  - Dynamic question rendering
  - Conditional logic
  - Form validation
  - Progress tracking
  - Error handling

### QuestionField.vue
- Props: `question`, `value`, `errors`
- Emits: `update`, `blur`, `focus`
- Renders appropriate input based on question type

## Event Handlers

### QuestionnaireCreatedHandler
- Updates `QuestionnaireReadModel`
- Indexes questions for search
- Triggers notifications

### QuestionnaireResponseSubmittedHandler
- Creates `QuestionnaireResponse` record
- Updates patient profile
- Triggers clinical review workflow
- Emits analytics events

## Migration Strategy

### Step 1: Export Legacy Questions
```php
// Export from questions table
$questions = Question::with('options')->get();
// Transform to new format
$formatted = $questions->map(fn($q) => [
    'id' => "q{$q->id}",
    'text' => $q->question,
    'type' => $q->type,
    'required' => $q->required,
    'options' => $q->options->pluck('option_value')->toArray(),
]);
```

### Step 2: Create Questionnaire
```php
// Create questionnaire aggregate
$command = new CreateQuestionnaire(
    questionnaireUuid: Str::uuid(),
    title: 'Comprehensive Health Screening',
    description: 'Initial health assessment',
    questions: $formatted->toArray(),
    createdBy: 1,
);
$commandBus->dispatch($command);
```

### Step 3: Verify Data
- Compare question counts
- Validate question structure
- Test conditional logic
- Verify options mapping

## Performance Considerations

1. **Caching**: Cache questionnaire JSON in Redis
2. **Pagination**: Load questions in batches for large forms
3. **Lazy Loading**: Load conditional questions on demand
4. **Indexing**: Index questionnaire_uuid, patient_id, status
5. **Query Optimization**: Use select() to limit columns

## Security

1. **Validation**: Validate all responses server-side
2. **Authorization**: Only authenticated users can submit
3. **Rate Limiting**: Limit submissions per user
4. **Data Sanitization**: Sanitize text inputs
5. **File Uploads**: Validate file types and size

## Testing

### Unit Tests
- Question rendering logic
- Validation rules
- Conditional logic evaluation

### Integration Tests
- Event handlers
- API endpoints
- Database operations

### Feature Tests
- Complete signup flow
- Form submission
- Error handling
- Data persistence

