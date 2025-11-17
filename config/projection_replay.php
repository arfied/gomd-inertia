<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Projection replay configuration
    |--------------------------------------------------------------------------
    |
    | This file defines how logical event_type strings map to concrete
    | DomainEvent classes, and how logical projection names map to the event
    | types they care about.
    |
    */

    'event_types' => [
        // Domain events for the patient aggregate
        'patient.enrolled' => App\Domain\Patient\Events\PatientEnrolled::class,
        'patient.demographics_updated' => App\Domain\Patient\Events\PatientDemographicsUpdated::class,
        'patient.document_uploaded' => App\Domain\Patient\Events\PatientDocumentUploaded::class,
    ],

    'projections' => [
        // Rebuilds the patient_enrollments read model
        'patient-enrollment' => [
            'patient.enrolled',
        ],

        // Rebuilds the patient demographics on the users table
        'patient-demographics' => [
            'patient.demographics_updated',
        ],

        // Rebuilds the patient documents projection over medical_records
        'patient-documents' => [
            'patient.document_uploaded',
        ],

        // Rebuilds the activity log entries related to patient events
        'patient-activity' => [
            'patient.enrolled',
        ],
    ],
];

