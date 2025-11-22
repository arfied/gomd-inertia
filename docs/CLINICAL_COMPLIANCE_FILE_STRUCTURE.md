# Clinical & Compliance - File Structure

## 📁 Complete Directory Structure

```
app/
├── Domain/
│   ├── Clinical/
│   │   ├── QuestionnaireAggregate.php
│   │   ├── ClinicalNoteAggregate.php
│   │   ├── ConsultationAggregate.php
│   │   └── Events/
│   │       ├── QuestionnaireCreated.php
│   │       ├── ResponseSubmitted.php
│   │       ├── ClinicalNoteRecorded.php
│   │       └── ConsultationScheduled.php
│   │
│   └── Compliance/
│       ├── ConsentAggregate.php
│       ├── AuditLogAggregate.php
│       ├── LicenseAggregate.php
│       └── Events/
│           ├── ConsentGranted.php
│           ├── AccessLogged.php
│           └── LicenseVerified.php
│
├── Services/
│   ├── Clinical/
│   │   └── AdaptiveQuestionnaireEngine.php
│   │
│   └── Compliance/
│       ├── HIPAAComplianceChecker.php
│       ├── RegulatoryReportingService.php
│       └── AuditTrailService.php
│
└── Models/
    ├── QuestionnaireReadModel.php
    ├── ClinicalNoteReadModel.php
    ├── ConsultationReadModel.php
    ├── AuditTrailReadModel.php
    ├── ConsentReadModel.php
    └── LicenseReadModel.php

config/
└── projection_replay.php (UPDATED)

docs/
├── CLINICAL_COMPLIANCE_IMPLEMENTATION.md
├── CLINICAL_COMPLIANCE_QUICK_START.md
├── CLINICAL_COMPLIANCE_COMPLETION_SUMMARY.md
└── CLINICAL_COMPLIANCE_FILE_STRUCTURE.md
```

## 📋 File Descriptions

### Clinical Domain

**QuestionnaireAggregate.php**
- Manages questionnaire lifecycle
- Tracks questions and responses
- Supports multiple response submissions
- Status: draft, active, archived

**ClinicalNoteAggregate.php**
- Manages clinical notes
- Tracks note type (progress, assessment, plan, SOAP)
- Supports attachments
- Records doctor and patient information

**ConsultationAggregate.php**
- Manages consultation scheduling
- Tracks status (scheduled, in_progress, completed, cancelled)
- Records duration and notes
- Tracks completion timestamp

**Clinical Events**
- QuestionnaireCreated: Initial questionnaire creation
- ResponseSubmitted: Patient submits responses
- ClinicalNoteRecorded: Doctor records clinical note
- ConsultationScheduled: Consultation is scheduled

### Compliance Domain

**ConsentAggregate.php**
- Manages patient consent
- Tracks consent type (treatment, privacy, data_sharing)
- Supports expiration dates
- Tracks terms version

**AuditLogAggregate.php**
- Manages audit trail entries
- Tracks access type (read, write, delete, export)
- Records IP address and user agent
- Supports full audit history

**LicenseAggregate.php**
- Manages provider licenses
- Tracks license type (MD, DO, NP, PA, RN)
- Supports expiration dates
- Records issuing body

**Compliance Events**
- ConsentGranted: Patient grants consent
- AccessLogged: Data access is logged
- LicenseVerified: Provider license is verified

### Services

**AdaptiveQuestionnaireEngine.php**
- Evaluates branching logic
- Supports conditional questions
- Calculates risk scores
- Dynamic question sequencing

**HIPAAComplianceChecker.php**
- Validates access authorization
- Checks minimum necessary principle
- Verifies role-based access
- Logs compliance violations

**RegulatoryReportingService.php**
- Generates breach notifications
- Creates compliance audit reports
- Generates DEA controlled substance reports
- Creates state medical board reports
- Exports to PDF and CSV

**AuditTrailService.php**
- Logs data access
- Logs data modifications
- Logs data exports
- Logs data deletions

### Read Models

**QuestionnaireReadModel**
- Scopes: active(), forPatient(), createdBy()
- Optimized for questionnaire queries

**ClinicalNoteReadModel**
- Scopes: forPatient(), byDoctor(), ofType(), recent()
- Optimized for clinical note queries

**ConsultationReadModel**
- Scopes: forPatient(), withDoctor(), upcoming(), completed()
- Optimized for consultation queries

**AuditTrailReadModel**
- Scopes: forPatient(), byUser(), ofType(), inDateRange(), suspicious()
- Optimized for audit log queries

**ConsentReadModel**
- Scopes: forPatient(), ofType(), expired(), expiringSoon()
- Optimized for consent queries

**LicenseReadModel**
- Scopes: forProvider(), verified(), expired(), expiringSoon(), ofType()
- Optimized for license queries

## 🔄 Event Flow

```
Aggregate Action
    ↓
recordThat(DomainEvent)
    ↓
Event stored in aggregate
    ↓
releaseEvents()
    ↓
EventStore::store(event)
    ↓
Event persisted to event_store table
    ↓
Event Handler triggered
    ↓
Read Model updated
    ↓
Query available via Read Model
```

## 🔗 Configuration Updates

**config/projection_replay.php**

Added event type mappings:
```php
'questionnaire.created' => App\Domain\Clinical\Events\QuestionnaireCreated::class,
'questionnaire.response_submitted' => App\Domain\Clinical\Events\ResponseSubmitted::class,
'clinical_note.recorded' => App\Domain\Clinical\Events\ClinicalNoteRecorded::class,
'consultation.scheduled' => App\Domain\Clinical\Events\ConsultationScheduled::class,
'consent.granted' => App\Domain\Compliance\Events\ConsentGranted::class,
'audit_log.access_logged' => App\Domain\Compliance\Events\AccessLogged::class,
'license.verified' => App\Domain\Compliance\Events\LicenseVerified::class,
```

Added projection definitions for all read models.

## ✨ Ready for Development

All files are created and configured. Next steps:
1. Create event handlers
2. Create database migrations
3. Create API endpoints
4. Create tests
5. Create UI components

