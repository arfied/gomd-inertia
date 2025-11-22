# Clinical & Compliance Frontend - Quick Start

## Importing Components

### Clinical Components
```typescript
import { 
  QuestionnaireForm, 
  ClinicalNoteEditor, 
  ConsultationScheduler 
} from '@/components/Clinical'
```

### Compliance Components
```typescript
import { 
  AuditTrailTable, 
  AuditTrailTimeline, 
  ConsentForm, 
  LicenseVerification 
} from '@/components/Compliance'
```

## Common Patterns

### 1. Questionnaire Flow
```vue
<script setup>
const handleSubmit = async (responses) => {
  const res = await fetch(`/api/questionnaires/${id}/responses`, {
    method: 'POST',
    body: JSON.stringify({ responses })
  })
}
</script>

<template>
  <QuestionnaireForm
    title="Health Assessment"
    :questions="questions"
    @submit="handleSubmit"
  />
</template>
```

### 2. Audit Trail with Export
```vue
<script setup>
const handleExport = async () => {
  const res = await fetch('/api/audit-trail/export')
  const data = await res.json()
  // Convert to CSV and download
}
</script>

<template>
  <AuditTrailTable
    :records="records"
    @export="handleExport"
    @view-details="viewDetails"
  />
</template>
```

### 3. Consent Management
```vue
<script setup>
const handleSubmit = async (consents) => {
  await fetch('/api/consents', {
    method: 'POST',
    body: JSON.stringify({ consents })
  })
}
</script>

<template>
  <ConsentForm @submit="handleSubmit" />
</template>
```

### 4. License Verification
```vue
<template>
  <LicenseVerification
    :licenses="licenses"
    @verify="verifyLicense"
  />
</template>
```

## Component Props

### QuestionnaireForm
- `title: string` - Form title
- `description?: string` - Form description
- `questions: Question[]` - Array of questions
- `loading?: boolean` - Loading state

### ClinicalNoteEditor
- `patientId?: string` - Patient ID
- `doctorId?: string` - Doctor ID
- `loading?: boolean` - Loading state

### ConsultationScheduler
- `patientId?: string` - Patient ID
- `doctorId?: string` - Doctor ID
- `loading?: boolean` - Loading state

### AuditTrailTable
- `records: AuditRecord[]` - Audit records
- `loading?: boolean` - Loading state
- `total?: number` - Total records count

### AuditTrailTimeline
- `records: AuditRecord[]` - Audit records

### ConsentForm
- `patientId?: string` - Patient ID
- `consentTypes?: ConsentType[]` - Consent types
- `loading?: boolean` - Loading state

### LicenseVerification
- `licenses: License[]` - License records
- `loading?: boolean` - Loading state

## Events

All components emit standard events:
- `submit` - Form submission with data
- `cancel` - User cancellation
- `view-details` - View detailed record
- `export` - Export data
- `verify` - Verify/renew action

## Styling

All components use Tailwind CSS classes:
- `bg-primary`, `bg-secondary` - Background colors
- `text-muted-foreground` - Muted text
- `border`, `rounded-lg` - Borders and radius
- `px-4 py-2` - Padding
- `flex`, `grid` - Layout

## API Endpoints

### Clinical
- `GET /api/questionnaires` - List questionnaires
- `POST /api/questionnaires/{id}/responses` - Submit responses
- `GET /api/clinical-notes` - List clinical notes
- `POST /api/clinical-notes` - Create clinical note
- `GET /api/consultations` - List consultations
- `POST /api/consultations` - Schedule consultation

### Compliance
- `GET /api/audit-trail` - List audit records
- `GET /api/audit-trail/export` - Export audit trail
- `GET /api/licenses` - List licenses
- `POST /api/licenses/{id}/verify` - Verify license
- `GET /api/consents` - List consents
- `POST /api/consents` - Grant consent

## Example: Complete Page

```vue
<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { AuditTrailTable } from '@/components/Compliance'

const records = ref([])
const loading = ref(false)

onMounted(async () => {
  loading.value = true
  const res = await fetch('/api/audit-trail')
  records.value = await res.json()
  loading.value = false
})

const handleExport = async () => {
  const res = await fetch('/api/audit-trail/export')
  const data = await res.json()
  // Download CSV
}
</script>

<template>
  <div class="space-y-6">
    <h1 class="text-3xl font-bold">Audit Trail</h1>
    <AuditTrailTable
      :records="records"
      :loading="loading"
      @export="handleExport"
    />
  </div>
</template>
```

## Customization

### Change Colors
Modify Tailwind classes in component templates:
```vue
<!-- Change primary color -->
<div class="bg-blue-500">...</div>
```

### Add Custom Styling
Use scoped styles:
```vue
<style scoped>
.custom-class {
  @apply px-4 py-2 rounded-lg;
}
</style>
```

### Extend Components
Create wrapper components:
```vue
<template>
  <QuestionnaireForm
    v-bind="$attrs"
    @submit="customSubmit"
  />
</template>
```

