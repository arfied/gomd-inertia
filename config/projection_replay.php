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
    ],

    'projections' => [
        // Rebuilds the patient_enrollments read model
        'patient-enrollment' => [
            'patient.enrolled',
        ],

        // Rebuilds the activity log entries related to patient events
        'patient-activity' => [
            'patient.enrolled',
        ],
    ],
];

