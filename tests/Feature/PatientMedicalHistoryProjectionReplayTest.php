<?php

use App\Models\FamilyMedicalCondition;
use App\Models\FamilyMedicalHistory;
use App\Models\MedicalSurgicalHistory;
use App\Models\MedicationHistory;
use App\Models\StoredEvent;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;

use function Pest\Laravel\assertDatabaseHas;

it('rebuilds patient medication history from event store via patient-medical-history projection', function () {
    if (! extension_loaded('pdo_sqlite')) {
        $this->markTestSkipped('pdo_sqlite extension is required for this test.');
    }

    $user = User::factory()->create();

    $patientUuid = 'patient-uuid-replay-medication';

    MedicationHistory::query()->delete();

    StoredEvent::create([
        'aggregate_uuid' => $patientUuid,
        'aggregate_type' => 'patient',
        'event_type' => 'patient.medication_added',
        'event_data' => [
            'user_id' => $user->id,
            'medication_id' => 123,
            'start_date' => '2024-05-01',
            'end_date' => '2024-05-10',
            'dosage' => '1 tablet',
            'frequency' => 'twice daily',
            'notes' => 'Take with food',
        ],
        'metadata' => ['source' => 'replay-test'],
        'occurred_at' => now(),
    ]);

    Artisan::call('projections:replay', [
        '--projection' => 'patient-medical-history',
        '--aggregate-type' => 'patient',
    ]);

    assertDatabaseHas('medication_histories', [
        'user_id' => $user->id,
        'medication_id' => 123,
        'start_date' => '2024-05-01',
        'end_date' => '2024-05-10',
        'dosage' => '1 tablet',
        'frequency' => 'twice daily',
        'notes' => 'Take with food',
    ]);
});

it('rebuilds patient visit summary (surgical and family history) from event store', function () {
    if (! extension_loaded('pdo_sqlite')) {
        $this->markTestSkipped('pdo_sqlite extension is required for this test.');
    }

    $user = User::factory()->create();

    $patientUuid = 'patient-uuid-replay-visit-summary';

    MedicalSurgicalHistory::query()->delete();
    FamilyMedicalHistory::query()->delete();
    FamilyMedicalCondition::query()->delete();

    StoredEvent::create([
        'aggregate_uuid' => $patientUuid,
        'aggregate_type' => 'patient',
        'event_type' => 'patient.visit_summary_recorded',
        'event_data' => [
            'patient_id' => $user->id,
            'past_injuries' => true,
            'past_injuries_details' => 'Fractured arm in 2020',
            'surgery' => true,
            'surgery_details' => 'Appendectomy in 2015',
            'chronic_conditions_details' => 'Hypertension managed with medication',
            'chronic_pain' => true,
            'chronic_pain_details' => 'Lower back pain',
            'family_history_conditions' => [
                'Diabetes',
                ['name' => 'Heart disease'],
            ],
        ],
        'metadata' => ['source' => 'replay-test'],
        'occurred_at' => now(),
    ]);

    Artisan::call('projections:replay', [
        '--projection' => 'patient-medical-history',
        '--aggregate-type' => 'patient',
    ]);

    assertDatabaseHas('medical_surgical_histories', [
        'patient_id' => $user->id,
        'past_injuries' => 1,
        'past_injuries_details' => 'Fractured arm in 2020',
        'surgery' => 1,
        'surgery_details' => 'Appendectomy in 2015',
        'chronic_conditions_details' => 'Hypertension managed with medication',
    ]);

    $familyHistory = FamilyMedicalHistory::where('patient_id', $user->id)->firstOrFail();

    assertDatabaseHas('family_medical_histories', [
        'id' => $familyHistory->id,
        'patient_id' => $user->id,
        'chronic_pain' => 1,
        'chronic_pain_details' => 'Lower back pain',
    ]);

    assertDatabaseHas('family_medical_conditions', [
        'family_medical_history_id' => $familyHistory->id,
        'name' => 'Diabetes',
    ]);

    assertDatabaseHas('family_medical_conditions', [
        'family_medical_history_id' => $familyHistory->id,
        'name' => 'Heart disease',
    ]);
});

