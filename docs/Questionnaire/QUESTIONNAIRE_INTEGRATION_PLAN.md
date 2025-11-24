# Questionnaire Integration Plan

## Overview
Integrate the legacy questionnaire system (hardcoded Blade template with category-based logic) into the new event-sourced architecture while leveraging the existing `questions` and `question_options` tables.

## Current State Analysis

### Legacy System (questions.blade.php)
- **Hardcoded fields**: 50+ textarea fields organized by medication category
- **Category-based logic**: Conditional rendering based on medication categories
- **Form framework**: Splade (server-side form handling)
- **Data storage**: `medical_questionnaires` table with denormalized columns
- **Issues**: Not scalable, difficult to maintain, tightly coupled to categories

### Database Structure
- **questions table**: Stores question definitions (service_id, question, type, parent_question_id)
- **question_options table**: Stores options for select/radio/checkbox questions
- **Relationship**: One-to-many (Question → QuestionOption)
- **Unused**: Currently not integrated with signup flow

## Integration Strategy

### Phase 1: Data Migration (Week 1)
**Goal**: Migrate questions from `questions` table to `QuestionnaireReadModel`

**Tasks**:
1. Create migration to populate `QuestionnaireReadModel` from `questions` table
2. Transform question structure:
   ```json
   {
     "id": "q1",
     "text": "Question text",
     "type": "text|select|radio|checkbox|textarea",
     "required": true,
     "options": ["Option 1", "Option 2"],
     "section": "cardiovascular|neurological|etc",
     "order": 1
   }
   ```
3. Create seeder for test questionnaires
4. Add tests to verify migration

### Phase 2: Event-Sourced Questionnaire Management (Week 2)
**Goal**: Create domain events and aggregate for questionnaire lifecycle

**Tasks**:
1. Create `QuestionnaireAggregate` in Clinical bounded context
2. Define domain events:
   - `QuestionnaireCreated` - New questionnaire defined
   - `QuestionAdded` - Question added to questionnaire
   - `QuestionnairePublished` - Questionnaire ready for use
   - `QuestionnaireArchived` - Questionnaire no longer active
3. Create command handlers:
   - `CreateQuestionnaireCommand`
   - `AddQuestionCommand`
   - `PublishQuestionnaireCommand`
4. Create event handlers to update `QuestionnaireReadModel`
5. Add comprehensive tests

### Phase 3: Dynamic Vue Component (Week 3)
**Goal**: Build reusable Vue component to render questions dynamically

**Tasks**:
1. Create `DynamicQuestionnaireForm.vue` component
   - Renders questions from JSON structure
   - Supports all question types (text, select, radio, checkbox, textarea)
   - Handles conditional logic (parent_question_id)
   - Validates required fields
   - Supports file uploads for documents
2. Create `QuestionField.vue` sub-component for individual questions
3. Implement form state management in Pinia store
4. Add client-side validation
5. Create tests for component rendering

### Phase 4: API Enhancements (Week 3)
**Goal**: Extend API to support dynamic questionnaire loading

**Tasks**:
1. Update `Api/QuestionnaireController` to:
   - Filter by medication category
   - Filter by condition type
   - Support pagination for large questionnaires
2. Add response submission endpoint:
   - `POST /api/questionnaires/{id}/submit`
   - Validate responses
   - Trigger `QuestionnaireResponseSubmitted` event
3. Add tests for new endpoints

### Phase 5: Signup Flow Integration (Week 4)
**Goal**: Replace hardcoded template with dynamic component

**Tasks**:
1. Update `SignupQuestionnaireStep.vue` to use `DynamicQuestionnaireForm.vue`
2. Implement conditional rendering based on selected medications
3. Add response persistence to signup aggregate
4. Update tests
5. Create migration guide for existing data

### Phase 6: Deprecation & Cleanup (Week 4)
**Goal**: Remove legacy code

**Tasks**:
1. Deprecate `questions.blade.php`
2. Archive old routes
3. Create data migration for existing responses
4. Update documentation

## Key Improvements

### Scalability
- ✅ Questions defined in database, not hardcoded
- ✅ Easy to add new questions without code changes
- ✅ Support for unlimited question types

### Maintainability
- ✅ Event-sourced for audit trail
- ✅ Reusable Vue component
- ✅ Centralized question management

### User Experience
- ✅ Dynamic form rendering
- ✅ Conditional logic support
- ✅ Better validation
- ✅ Progress tracking

### Data Quality
- ✅ Structured responses
- ✅ Audit trail of changes
- ✅ Easy to query and analyze

## Database Schema Updates

### QuestionnaireReadModel Enhancement
```sql
ALTER TABLE questionnaire_read_model ADD COLUMN (
  section VARCHAR(255),
  category VARCHAR(255),
  medication_ids JSON,
  condition_ids JSON
);
```

### New Table: QuestionnaireResponse
```sql
CREATE TABLE questionnaire_responses (
  id BIGINT PRIMARY KEY,
  questionnaire_uuid VARCHAR(255),
  patient_id VARCHAR(255),
  responses JSON,
  submitted_at TIMESTAMP,
  created_at TIMESTAMP
);
```

## Testing Strategy

1. **Unit Tests**: Question rendering, validation logic
2. **Integration Tests**: Event handlers, API endpoints
3. **Feature Tests**: Complete signup flow with questionnaire
4. **E2E Tests**: User journey from medication selection to submission

## Timeline
- **Week 1**: Phase 1 (Data Migration)
- **Week 2**: Phase 2 (Event-Sourced Management)
- **Week 3**: Phase 3 & 4 (Vue Component & API)
- **Week 4**: Phase 5 & 6 (Integration & Cleanup)

## Success Criteria
- ✅ All questions migrated to new system
- ✅ 100% test coverage for new code
- ✅ Signup flow works with dynamic questionnaire
- ✅ No data loss during migration
- ✅ Performance: <500ms to load questionnaire

