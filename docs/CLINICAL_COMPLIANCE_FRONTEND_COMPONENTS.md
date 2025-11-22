# Clinical & Compliance Frontend Components

## Overview

Complete Vue 3 + TypeScript frontend components for Clinical and Compliance features, built with Tailwind CSS and shadcn/ui components. All components are production-ready and follow the existing codebase patterns.

## Components Created

### Clinical Components

#### 1. **QuestionnaireForm** (`resources/js/components/Clinical/QuestionnaireForm.vue`)
- Adaptive multi-step questionnaire form
- Supports: text, textarea, checkbox, radio question types
- Progress bar showing current step
- Previous/Next navigation
- Emits: `submit`, `cancel`

**Usage:**
```vue
<QuestionnaireForm
  title="Health Assessment"
  description="Please answer the following questions"
  :questions="questions"
  @submit="handleSubmit"
  @cancel="handleCancel"
/>
```

#### 2. **ClinicalNoteEditor** (`resources/js/components/Clinical/ClinicalNoteEditor.vue`)
- Rich text editor for clinical notes
- Note type selection (general, diagnosis, treatment, follow-up, lab results)
- File attachment support with drag-and-drop
- Emits: `submit`, `cancel`

**Usage:**
```vue
<ClinicalNoteEditor
  :patient-id="patientId"
  :doctor-id="doctorId"
  @submit="handleSubmit"
  @cancel="handleCancel"
/>
```

#### 3. **ConsultationScheduler** (`resources/js/components/Clinical/ConsultationScheduler.vue`)
- Date/time picker for scheduling consultations
- Reason and notes fields
- Minimum 1 hour from now validation
- Summary preview
- Emits: `submit`, `cancel`

**Usage:**
```vue
<ConsultationScheduler
  :patient-id="patientId"
  :doctor-id="doctorId"
  @submit="handleSubmit"
  @cancel="handleCancel"
/>
```

### Compliance Components

#### 4. **AuditTrailTable** (`resources/js/components/Compliance/AuditTrailTable.vue`)
- Sortable, filterable data table
- Search by patient ID, user ID, or action
- Filter by action type and status
- Export functionality
- Emits: `view-details`, `export`, `filter`

**Usage:**
```vue
<AuditTrailTable
  :records="auditRecords"
  :total="totalRecords"
  @view-details="viewDetails"
  @export="handleExport"
/>
```

#### 5. **AuditTrailTimeline** (`resources/js/components/Compliance/AuditTrailTimeline.vue`)
- Chronological timeline view of audit events
- Visual indicators for action types
- Status badges (success/failure)
- Expandable details for each event
- Emits: `view-details`

**Usage:**
```vue
<AuditTrailTimeline
  :records="auditRecords"
  @view-details="viewDetails"
/>
```

#### 6. **ConsentForm** (`resources/js/components/Compliance/ConsentForm.vue`)
- Multi-consent form with required/optional fields
- Checkbox-based consent acceptance
- Legal notice section
- Validation for required consents
- Emits: `submit`, `cancel`

**Usage:**
```vue
<ConsentForm
  :patient-id="patientId"
  :consent-types="consentTypes"
  @submit="handleSubmit"
  @cancel="handleCancel"
/>
```

#### 7. **LicenseVerification** (`resources/js/components/Compliance/LicenseVerification.vue`)
- Display active, expired, and suspended licenses
- License details (number, state, dates)
- Status indicators with color coding
- Expiration tracking
- Emits: `verify`, `view-details`

**Usage:**
```vue
<LicenseVerification
  :licenses="licenses"
  @verify="handleVerify"
  @view-details="viewDetails"
/>
```

## Example Pages

### 1. **Questionnaires Page** (`resources/js/pages/clinical/Questionnaires.vue`)
- List available questionnaires
- Start questionnaire flow
- Submit responses to API
- Integrates QuestionnaireForm component

### 2. **Audit Trail Page** (`resources/js/pages/compliance/AuditTrail.vue`)
- Toggle between table and timeline views
- Filter and search audit records
- Export audit trail to CSV
- View detailed audit records
- Integrates AuditTrailTable and AuditTrailTimeline

### 3. **Compliance Dashboard** (`resources/js/pages/compliance/Dashboard.vue`)
- Summary cards (active/expired licenses)
- Consent management section
- License verification section
- Integrates ConsentForm and LicenseVerification

## Styling

All components use:
- **Tailwind CSS** for styling
- **shadcn/ui** components (Button, Card, Input, Label, Checkbox, Badge)
- Consistent color scheme and spacing
- Responsive design (mobile-first)
- Dark mode support via Tailwind

## API Integration

Components emit events that pages handle:
- `submit` - Form submission with data
- `cancel` - User cancellation
- `view-details` - View detailed record
- `export` - Export data
- `filter` - Apply filters

Pages handle API calls:
- `GET /api/questionnaires` - List questionnaires
- `POST /api/questionnaires/{id}/responses` - Submit responses
- `GET /api/audit-trail` - List audit records
- `GET /api/audit-trail/export` - Export audit trail
- `GET /api/licenses` - List licenses
- `POST /api/licenses/{id}/verify` - Verify license
- `POST /api/consents` - Grant consent

## File Structure

```
resources/js/
├── components/
│   ├── Clinical/
│   │   ├── QuestionnaireForm.vue
│   │   ├── ClinicalNoteEditor.vue
│   │   ├── ConsultationScheduler.vue
│   │   └── index.ts
│   └── Compliance/
│       ├── AuditTrailTable.vue
│       ├── AuditTrailTimeline.vue
│       ├── ConsentForm.vue
│       ├── LicenseVerification.vue
│       └── index.ts
└── pages/
    ├── clinical/
    │   └── Questionnaires.vue
    └── compliance/
        ├── AuditTrail.vue
        └── Dashboard.vue
```

## Next Steps

1. Add routes to `resources/js/routes/` for the new pages
2. Integrate with existing navigation/sidebar
3. Add more clinical components (medical history, prescriptions, etc.)
4. Implement real-time updates with WebSockets
5. Add more compliance features (HIPAA checks, regulatory reports)

