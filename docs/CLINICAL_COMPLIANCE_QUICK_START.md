# Clinical & Compliance Quick Start Guide

## 📋 Clinical Domain

### Create a Questionnaire
```php
use App\Domain\Clinical\QuestionnaireAggregate;
use Illuminate\Support\Str;

$uuid = Str::uuid()->toString();
$aggregate = QuestionnaireAggregate::create($uuid, [
    'title' => 'Patient Health Assessment',
    'description' => 'Initial health questionnaire',
    'questions' => [
        ['id' => 'q1', 'text' => 'Do you have allergies?', 'type' => 'yes_no'],
        ['id' => 'q2', 'text' => 'Current medications?', 'type' => 'text'],
    ],
    'created_by' => auth()->id(),
    'patient_id' => $patientId,
], ['source' => 'api']);

// Release events and store them
$events = $aggregate->releaseEvents();
foreach ($events as $event) {
    app(EventStoreContract::class)->store($event);
}
```

### Submit Questionnaire Responses
```php
$aggregate->submitResponse([
    'questionnaire_id' => $uuid,
    'patient_id' => $patientId,
    'responses' => ['q1' => 'yes', 'q2' => 'Aspirin'],
    'submitted_at' => now()->toIso8601String(),
]);
```

### Use Adaptive Questionnaire Engine
```php
use App\Services\Clinical\AdaptiveQuestionnaireEngine;

$engine = app(AdaptiveQuestionnaireEngine::class);

// Get next questions based on responses
$nextQuestions = $engine->evaluateBranching($allQuestions, $responses);

// Calculate risk score
$riskScore = $engine->calculateRiskScore($responses, $scoringRules);
```

### Query Questionnaires
```php
use App\Models\QuestionnaireReadModel;

// Get active questionnaires for a patient
$questionnaires = QuestionnaireReadModel::forPatient($patientId)->active()->get();

// Get questionnaires created by a user
$myQuestionnaires = QuestionnaireReadModel::createdBy(auth()->id())->get();
```

## 🔐 Compliance Domain

### Log Data Access
```php
use App\Services\Compliance\AuditTrailService;

$auditService = app(AuditTrailService::class);

$auditService->logAccess(
    patientId: $patientId,
    accessedBy: auth()->id(),
    accessType: 'read',
    resource: 'patient_record',
    context: [
        'ip_address' => request()->ip(),
        'user_agent' => request()->userAgent(),
    ]
);
```

### Check HIPAA Compliance
```php
use App\Services\Compliance\HIPAAComplianceChecker;

$checker = app(HIPAAComplianceChecker::class);

$result = $checker->checkAccess(
    userId: auth()->id(),
    patientId: $patientId,
    accessType: 'read'
);

if (!$result['compliant']) {
    foreach ($result['violations'] as $violation) {
        $checker->logViolation(auth()->id(), $patientId, $violation);
    }
}
```

### Grant Patient Consent
```php
use App\Domain\Compliance\ConsentAggregate;

$uuid = Str::uuid()->toString();
$aggregate = ConsentAggregate::create($uuid, [
    'patient_id' => $patientId,
    'consent_type' => 'treatment',
    'granted_by' => auth()->id(),
    'granted_at' => now()->toIso8601String(),
    'expires_at' => now()->addYear()->toIso8601String(),
    'terms_version' => '1.0',
]);
```

### Verify Provider License
```php
use App\Domain\Compliance\LicenseAggregate;

$uuid = Str::uuid()->toString();
$aggregate = LicenseAggregate::create($uuid, [
    'provider_id' => $providerId,
    'license_number' => 'MD123456',
    'license_type' => 'MD',
    'verified_at' => now()->toIso8601String(),
    'expires_at' => now()->addYears(2)->toIso8601String(),
    'issuing_body' => 'State Medical Board',
]);
```

### Generate Compliance Reports
```php
use App\Services\Compliance\RegulatoryReportingService;

$reportService = app(RegulatoryReportingService::class);

// Generate breach notification report
$report = $reportService->generateBreachNotificationReport([
    'breach_date' => now()->subDays(5),
    'discovery_date' => now(),
    'affected_count' => 150,
    'description' => 'Unauthorized access to patient records',
    'mitigation' => ['Reset passwords', 'Notify patients'],
]);

// Export to PDF
$pdfFile = $reportService->exportToPDF($report);
```

### Query Audit Trail
```php
use App\Models\AuditTrailReadModel;

// Get audit logs for a patient
$logs = AuditTrailReadModel::forPatient($patientId)->get();

// Get suspicious access patterns
$suspicious = AuditTrailReadModel::suspicious()->get();

// Get logs in date range
$logs = AuditTrailReadModel::inDateRange($startDate, $endDate)->get();
```

## 🔗 Integration Points

### Middleware for Audit Logging
```php
// In your middleware
$auditService->logAccess(
    auth()->id(),
    $request->route('patient_id'),
    'read',
    'patient_record'
);
```

### Event Handlers
Create handlers in `app/Application/Clinical/Handlers/` and `app/Application/Compliance/Handlers/` to update read models when events are published.

### API Endpoints
Create endpoints in `routes/api.php` for:
- POST `/api/questionnaires` - Create questionnaire
- POST `/api/questionnaires/{id}/responses` - Submit responses
- GET `/api/clinical-notes` - List clinical notes
- GET `/api/consultations` - List consultations
- GET `/api/audit-trail` - View audit logs
- GET `/api/consents` - View patient consents
- GET `/api/licenses` - Verify provider licenses

