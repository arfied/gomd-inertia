# Questionnaire Integration - Quick Start Guide

## Executive Summary

You have a legacy questionnaire system (hardcoded Blade template) that needs to be integrated into the new event-sourced architecture. The good news: you already have the database structure (`questions` and `question_options` tables) and the new infrastructure (`QuestionnaireReadModel`, API endpoints, Vue components).

## What You Have

✅ **Database**: `questions` table with 50+ questions, `question_options` for select/radio/checkbox  
✅ **Read Model**: `QuestionnaireReadModel` with JSON questions field  
✅ **API**: `/api/questionnaires` endpoint (just created)  
✅ **Frontend**: `SignupQuestionnaireStep.vue` component  
✅ **Event System**: Event-sourced architecture ready to use  

## What You Need to Build

### 1. **Data Migration** (Priority: HIGH)
Migrate questions from `questions` table to `QuestionnaireReadModel`

**Quick Start**:
```bash
# Create migration
php artisan make:migration MigrateQuestionsToQuestionnaireReadModel

# Create seeder
php artisan make:seeder QuestionnaireSeeder

# Run migration
php artisan migrate
php artisan db:seed --class=QuestionnaireSeeder
```

**Key Transformation**:
```php
// From: questions table (50+ rows)
// To: QuestionnaireReadModel (1 row with JSON questions array)

$questions = Question::with('options')->get();
$formatted = $questions->map(fn($q) => [
    'id' => "q{$q->id}",
    'text' => $q->question,
    'type' => $q->type,
    'required' => $q->required,
    'options' => $q->options->pluck('option_value')->toArray(),
    'section' => $this->mapToSection($q->service_id),
]);

QuestionnaireReadModel::create([
    'questionnaire_uuid' => Str::uuid(),
    'title' => 'Comprehensive Health Screening',
    'questions' => $formatted->toArray(),
    'status' => 'active',
]);
```

### 2. **Domain Events** (Priority: HIGH)
Create event-sourced questionnaire management

**Files to Create**:
- `app/Domain/Clinical/Events/QuestionnaireCreated.php`
- `app/Domain/Clinical/Events/QuestionnaireResponseSubmitted.php`
- `app/Domain/Clinical/Aggregates/QuestionnaireAggregate.php`
- `app/Domain/Clinical/Commands/CreateQuestionnaireCommand.php`
- `app/Domain/Clinical/Handlers/CreateQuestionnaireHandler.php`

**Pattern**: Follow existing `MedicationAggregate` and `ConditionAggregate`

### 3. **Dynamic Vue Component** (Priority: MEDIUM)
Replace hardcoded template with dynamic form renderer

**Create**: `resources/js/components/DynamicQuestionnaireForm.vue`

**Features**:
- Render questions from JSON
- Support all question types
- Handle conditional logic
- Client-side validation
- Progress tracking

**Usage**:
```vue
<DynamicQuestionnaireForm
  :questions="questions"
  :loading="loading"
  @submit="handleSubmit"
/>
```

### 4. **API Enhancement** (Priority: MEDIUM)
Extend questionnaire API for responses

**Add Endpoint**: `POST /api/questionnaires/{id}/submit`

**Responsibilities**:
- Validate responses
- Trigger `QuestionnaireResponseSubmitted` event
- Store responses in database
- Return success/error

### 5. **Response Storage** (Priority: MEDIUM)
Create table for questionnaire responses

**Migration**:
```php
Schema::create('questionnaire_responses', function (Blueprint $table) {
    $table->id();
    $table->string('questionnaire_uuid');
    $table->string('patient_id');
    $table->json('responses');
    $table->timestamp('submitted_at');
    $table->timestamps();
    $table->index(['questionnaire_uuid', 'patient_id']);
});
```

## Implementation Roadmap

### Week 1: Foundation
- [ ] Migrate questions to QuestionnaireReadModel
- [ ] Create domain events and aggregate
- [ ] Add tests for migration and events

### Week 2: Frontend
- [ ] Build DynamicQuestionnaireForm component
- [ ] Implement conditional logic
- [ ] Add form validation

### Week 3: Integration
- [ ] Create response submission endpoint
- [ ] Update SignupQuestionnaireStep to use new component
- [ ] Add end-to-end tests

### Week 4: Polish
- [ ] Performance optimization
- [ ] Error handling
- [ ] Documentation

## Key Decisions

### 1. **Question Storage**
- ✅ Store in `QuestionnaireReadModel.questions` as JSON
- ❌ Don't keep `questions` table (legacy)
- Reason: Event-sourced, easier to version, better performance

### 2. **Conditional Logic**
- ✅ Use `parent_question_id` from legacy system
- ✅ Implement in Vue component (client-side)
- ✅ Validate on server (server-side)
- Reason: Better UX, security

### 3. **Response Storage**
- ✅ Create separate `questionnaire_responses` table
- ✅ Store as JSON for flexibility
- ✅ Index by questionnaire_uuid and patient_id
- Reason: Easy to query, audit trail

### 4. **API Design**
- ✅ Keep `/api/questionnaires` for loading
- ✅ Add `/api/questionnaires/{id}/submit` for responses
- ✅ No authentication required for signup flow
- Reason: Consistent with existing pattern

## Comparison: Legacy vs New

| Aspect | Legacy | New |
|--------|--------|-----|
| Questions | Hardcoded in Blade | Database + JSON |
| Rendering | Server-side (Blade) | Client-side (Vue) |
| Validation | Server-side only | Client + Server |
| Scalability | Limited (50 fields) | Unlimited |
| Maintainability | Hard (code changes) | Easy (DB changes) |
| Audit Trail | None | Full event history |
| Testing | Difficult | Easy |
| Performance | Slower (server render) | Faster (client render) |

## Next Steps

1. **Review** this plan with your team
2. **Start** with Phase 1 (Data Migration)
3. **Test** thoroughly at each phase
4. **Deploy** incrementally
5. **Monitor** performance and user feedback

## Questions?

Refer to:
- `QUESTIONNAIRE_INTEGRATION_PLAN.md` - Detailed phases
- `QUESTIONNAIRE_TECHNICAL_SPEC.md` - Technical details
- Existing code: `MedicationAggregate`, `ConditionAggregate` for patterns

