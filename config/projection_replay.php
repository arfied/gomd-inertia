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
        'patient.allergy_recorded' => App\Domain\Patient\Events\PatientAllergyRecorded::class,
        'patient.condition_recorded' => App\Domain\Patient\Events\PatientConditionRecorded::class,
        'patient.medication_added' => App\Domain\Patient\Events\PatientMedicationAdded::class,
        'patient.visit_summary_recorded' => App\Domain\Patient\Events\PatientVisitSummaryRecorded::class,
        'order.created' => App\Domain\Order\Events\OrderCreated::class,
        'order.prescription_created' => App\Domain\Order\Events\PrescriptionCreated::class,
        'order.prescription_failed' => App\Domain\Order\Events\PrescriptionFailed::class,
        'order.inventory_reserved' => App\Domain\Order\Events\InventoryReserved::class,
        'order.inventory_reservation_failed' => App\Domain\Order\Events\InventoryReservationFailed::class,
        'order.shipment_initiated' => App\Domain\Order\Events\ShipmentInitiated::class,
        'order.shipment_initiation_failed' => App\Domain\Order\Events\ShipmentInitiationFailed::class,
        'order.prescription_cancelled' => App\Domain\Order\Events\PrescriptionCancelled::class,
        'order.inventory_released' => App\Domain\Order\Events\InventoryReleased::class,
        'order_fulfillment_saga.started' => App\Domain\Order\Events\OrderFulfillmentSagaStarted::class,
        'order_fulfillment_saga.state_changed' => App\Domain\Order\Events\OrderFulfillmentSagaStateChanged::class,
        'order_fulfillment_saga.compensation_recorded' => App\Domain\Order\Events\CompensationRecorded::class,
        'order_fulfillment_saga.failed' => App\Domain\Order\Events\OrderFulfillmentSagaFailed::class,
        'order_fulfillment_saga.completed' => App\Domain\Order\Events\OrderFulfillmentSagaCompleted::class,
        'prescription.created' => App\Domain\Prescription\Events\PrescriptionCreated::class,
        'medication.created' => App\Domain\MedicationCatalog\Events\MedicationCreated::class,
        'medication.updated' => App\Domain\MedicationCatalog\Events\MedicationUpdated::class,
        'formulary.created' => App\Domain\MedicationCatalog\Events\FormularyCreated::class,
        'formulary.updated' => App\Domain\MedicationCatalog\Events\FormularyUpdated::class,
        'formulary.medication_added' => App\Domain\MedicationCatalog\Events\MedicationAddedToFormulary::class,
        'formulary.medication_removed' => App\Domain\MedicationCatalog\Events\MedicationRemovedFromFormulary::class,
        'commission.earned' => App\Domain\Commission\Events\CommissionEarned::class,
        'commission.cancelled' => App\Domain\Commission\Events\CommissionCancelled::class,
        'payout.requested' => App\Domain\Commission\Events\PayoutRequested::class,
        'payout.processed' => App\Domain\Commission\Events\PayoutProcessed::class,
        'referral_hierarchy.created' => App\Domain\Commission\Events\ReferralHierarchyCreated::class,
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

        // Rebuilds the medical history projections over legacy tables
        'patient-medical-history' => [
            'patient.allergy_recorded',
            'patient.condition_recorded',
            'patient.medication_added',
            'patient.visit_summary_recorded',
        ],

        // Rebuilds the medication orders projection over legacy medication_orders
        'order' => [
            'order.created',
        ],

        // Rebuilds the prescriptions projection over legacy prescriptions
        'prescription' => [
            'prescription.created',
        ],

        // Rebuilds the medication search index and medications read model
        'medication-catalog' => [
            'medication.created',
            'medication.updated',
            'formulary.created',
            'formulary.updated',
            'formulary.medication_added',
            'formulary.medication_removed',
        ],

        // Rebuilds the commission dashboard projection over agent_commissions
        'commission-dashboard' => [
            'commission.earned',
            'commission.cancelled',
        ],

        // Rebuilds the payout history projection over agent_payouts
        'payout-history' => [
            'payout.requested',
            'payout.processed',
        ],

        // Rebuilds the referral hierarchy projection
        'referral-hierarchy' => [
            'referral_hierarchy.created',
        ],
    ],
];

