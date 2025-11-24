# Questionnaire Integration - Implementation Checklist

## Phase 1: Data Migration ✓

### Database
- [ ] Create migration: `MigrateQuestionsToQuestionnaireReadModel`
- [ ] Transform questions from `questions` table to JSON format
- [ ] Map `question_options` to options array
- [ ] Map service_id to section names
- [ ] Create seeder: `QuestionnaireSeeder`
- [ ] Run migration and verify data
- [ ] Backup original `questions` table

### Testing
- [ ] Test migration with sample data
- [ ] Verify question count matches
- [ ] Verify options are correctly mapped
- [ ] Test conditional questions (parent_question_id)
- [ ] Create feature test for migration

### Documentation
- [ ] Document question structure
- [ ] Document section mapping
- [ ] Create rollback procedure

---

## Phase 2: Event-Sourced Management ✓

### Domain Events
- [ ] Create `QuestionnaireCreated` event
- [ ] Create `QuestionAdded` event
- [ ] Create `QuestionnairePublished` event
- [ ] Create `QuestionnaireResponseSubmitted` event
- [ ] Add `fromStoredEventData()` to all events
- [ ] Add event properties and validation

### Aggregate
- [ ] Create `QuestionnaireAggregate` class
- [ ] Implement `create()` factory method
- [ ] Implement `addQuestion()` method
- [ ] Implement `publish()` method
- [ ] Implement `submitResponse()` method
- [ ] Add event application logic

### Commands
- [ ] Create `CreateQuestionnaireCommand`
- [ ] Create `AddQuestionCommand`
- [ ] Create `PublishQuestionnaireCommand`
- [ ] Create `SubmitResponseCommand`

### Handlers
- [ ] Create `CreateQuestionnaireHandler`
- [ ] Create `QuestionnaireCreatedHandler` (updates read model)
- [ ] Create `QuestionnaireResponseSubmittedHandler`
- [ ] Register handlers in command bus

### Testing
- [ ] Unit tests for aggregate
- [ ] Unit tests for events
- [ ] Integration tests for handlers
- [ ] Test event rehydration
- [ ] Test read model updates

---

## Phase 3: Dynamic Vue Component ✓

### Components
- [ ] Create `DynamicQuestionnaireForm.vue`
  - [ ] Props: questions, initialResponses, loading
  - [ ] Emits: submit, update, error
  - [ ] Form state management
  - [ ] Conditional logic
  - [ ] Progress tracking
  
- [ ] Create `QuestionField.vue`
  - [ ] Props: question, value, errors
  - [ ] Emits: update, blur, focus
  - [ ] Support all question types
  - [ ] Error display
  - [ ] Accessibility

- [ ] Create question type components
  - [ ] TextInput.vue
  - [ ] TextArea.vue
  - [ ] Select.vue
  - [ ] Radio.vue
  - [ ] Checkbox.vue
  - [ ] DatePicker.vue
  - [ ] FileUpload.vue

### Features
- [ ] Dynamic question rendering
- [ ] Conditional logic (parent_question_id)
- [ ] Client-side validation
- [ ] Error handling
- [ ] Loading states
- [ ] Progress tracking
- [ ] Accessibility (ARIA labels)

### Testing
- [ ] Unit tests for components
- [ ] Test question rendering
- [ ] Test conditional logic
- [ ] Test validation
- [ ] Test error handling
- [ ] Snapshot tests

---

## Phase 4: API & Integration ✓

### API Endpoints
- [ ] Enhance `GET /api/questionnaires`
  - [ ] Filter by medication_name
  - [ ] Filter by condition_id
  - [ ] Pagination support
  
- [ ] Create `POST /api/questionnaires/{id}/submit`
  - [ ] Validate responses
  - [ ] Dispatch event
  - [ ] Return success/error
  - [ ] Error handling

### Database
- [ ] Create `questionnaire_responses` table
  - [ ] questionnaire_uuid
  - [ ] patient_id
  - [ ] responses (JSON)
  - [ ] submitted_at
  - [ ] Indexes

### Integration
- [ ] Update `SignupQuestionnaireStep.vue`
  - [ ] Load questionnaire on mount
  - [ ] Use `DynamicQuestionnaireForm.vue`
  - [ ] Handle submission
  - [ ] Update signup store
  - [ ] Navigate to next step

- [ ] Update `signupStore.ts`
  - [ ] Add `questionnaireResponses` state
  - [ ] Add `setQuestionnaireResponses()` action
  - [ ] Persist to backend

### Testing
- [ ] Feature test: Load questionnaire
- [ ] Feature test: Submit responses
- [ ] Feature test: Validation errors
- [ ] Feature test: Complete signup flow
- [ ] E2E test: User journey

---

## Phase 5: Deprecation & Cleanup ✓

### Legacy Code
- [ ] Archive `questions.blade.php`
- [ ] Archive legacy routes
- [ ] Create deprecation notice
- [ ] Document migration path

### Data Migration
- [ ] Migrate existing responses
- [ ] Update foreign keys
- [ ] Verify data integrity
- [ ] Create rollback procedure

### Documentation
- [ ] Update API documentation
- [ ] Update component documentation
- [ ] Create migration guide
- [ ] Update README

### Monitoring
- [ ] Monitor error rates
- [ ] Monitor performance
- [ ] Collect user feedback
- [ ] Track adoption

---

## Quality Assurance

### Code Quality
- [ ] Code review completed
- [ ] Linting passed (ESLint, PHP CS)
- [ ] Type checking passed (TypeScript)
- [ ] No console errors/warnings

### Testing
- [ ] Unit test coverage >90%
- [ ] Integration tests passing
- [ ] Feature tests passing
- [ ] E2E tests passing
- [ ] All tests green

### Performance
- [ ] Questionnaire loads <500ms
- [ ] Form renders smoothly
- [ ] No memory leaks
- [ ] Optimized queries

### Security
- [ ] Input validation
- [ ] CSRF protection
- [ ] Rate limiting
- [ ] Authorization checks
- [ ] Data sanitization

### Accessibility
- [ ] ARIA labels
- [ ] Keyboard navigation
- [ ] Screen reader support
- [ ] Color contrast
- [ ] Focus management

---

## Deployment

### Pre-Deployment
- [ ] Backup database
- [ ] Create rollback plan
- [ ] Notify stakeholders
- [ ] Schedule maintenance window

### Deployment
- [ ] Run migrations
- [ ] Run seeders
- [ ] Deploy code
- [ ] Clear caches
- [ ] Verify endpoints

### Post-Deployment
- [ ] Monitor error logs
- [ ] Monitor performance
- [ ] Verify functionality
- [ ] Collect user feedback
- [ ] Document issues

---

## Sign-Off

- [ ] Product Owner Approval
- [ ] QA Sign-Off
- [ ] Security Review
- [ ] Performance Review
- [ ] Documentation Complete

---

## Notes

**Start Date**: ___________  
**Completion Date**: ___________  
**Issues/Blockers**: ___________  
**Lessons Learned**: ___________

