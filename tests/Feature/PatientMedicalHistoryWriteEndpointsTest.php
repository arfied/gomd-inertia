<?php

use App\Models\Allergy;
use App\Models\FamilyMedicalCondition;
use App\Models\FamilyMedicalHistory;
use App\Models\MedicalCondition;
use App\Models\MedicalSurgicalHistory;
use App\Models\Medication;
use App\Models\MedicationHistory;
use App\Models\PatientEnrollment;
use App\Models\StoredEvent;
use App\Models\User;
use Illuminate\Support\Str;

it('requires authentication and staff role for medical history write endpoints', function () {
    $patientUuid = (string) Str::uuid();

    // Guest
    $this->postJson(route('patients.medical-history.allergies.store', ['patientUuid' => $patientUuid]))
        ->assertStatus(401);

    // Non-staff authenticated user
    $user = User::factory()->create();
    $user->role = 'patient';

    $this->actingAs($user);

    $this->postJson(route('patients.medical-history.allergies.store', ['patientUuid' => $patientUuid]), [
        'allergen' => 'Peanuts',
    ])->assertStatus(403);
});

it('records an allergy for a patient via staff endpoint', function () {
    $staff = User::factory()->create();
    $staff->role = 'staff';

    $patient = User::factory()->create();
    $patient->role = 'patient';

    $patientUuid = (string) Str::uuid();

    PatientEnrollment::create([
        'patient_uuid' => $patientUuid,
        'user_id' => $patient->id,
        'source' => 'manual',
        'metadata' => null,
        'enrolled_at' => now(),
    ]);

    $this->actingAs($staff);

    $response = $this->postJson(route('patients.medical-history.allergies.store', ['patientUuid' => $patientUuid]), [
        'allergen' => 'Peanuts',
        'reaction' => 'Anaphylaxis',
        'severity' => 'severe',
        'notes' => 'Carries EpiPen',
    ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('allergies', [
        'user_id' => $patient->id,
        'allergen' => 'Peanuts',
        'reaction' => 'Anaphylaxis',
        'severity' => 'severe',
        'notes' => 'Carries EpiPen',
    ]);

    $hasEvent = StoredEvent::query()
        ->where('aggregate_type', 'patient')
        ->where('event_type', 'patient.allergy_recorded')
        ->where('event_data->user_id', $patient->id)
        ->where('event_data->allergen', 'Peanuts')
        ->exists();

    expect($hasEvent)->toBeTrue();

    $response->assertJsonPath('medical_history.allergies.0.allergen', 'Peanuts');
});

it('records a condition for a patient via staff endpoint', function () {
    $staff = User::factory()->create();
    $staff->role = 'staff';

    $patient = User::factory()->create();
    $patient->role = 'patient';

    $patientUuid = (string) Str::uuid();

    PatientEnrollment::create([
        'patient_uuid' => $patientUuid,
        'user_id' => $patient->id,
        'source' => 'manual',
        'metadata' => null,
        'enrolled_at' => now(),
    ]);

    $this->actingAs($staff);

    $response = $this->postJson(route('patients.medical-history.conditions.store', ['patientUuid' => $patientUuid]), [
        'condition_name' => 'Hypertension',
        'diagnosed_at' => '2024-01-01',
        'notes' => 'Under control',
        'had_condition_before' => true,
        'is_chronic' => true,
    ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('medical_conditions', [
        'patient_id' => $patient->id,
        'condition_name' => 'Hypertension',
        'diagnosed_at' => '2024-01-01',
        'notes' => 'Under control',
        'had_condition_before' => 1,
        'is_chronic' => 1,
    ]);

    $hasEvent = StoredEvent::query()
        ->where('aggregate_type', 'patient')
        ->where('event_type', 'patient.condition_recorded')
        ->where('event_data->patient_id', $patient->id)
        ->where('event_data->condition_name', 'Hypertension')
        ->exists();

    expect($hasEvent)->toBeTrue();
});

it('records a medication for a patient via staff endpoint', function () {
    $staff = User::factory()->create();
    $staff->role = 'staff';

    $patient = User::factory()->create();
    $patient->role = 'patient';

    $medication = Medication::create([
        'name' => 'Ibuprofen',
        'dosage_form' => 'tablet',
        'strength' => '200mg',
    ]);

    $patientUuid = (string) Str::uuid();

    PatientEnrollment::create([
        'patient_uuid' => $patientUuid,
        'user_id' => $patient->id,
        'source' => 'manual',
        'metadata' => null,
        'enrolled_at' => now(),
    ]);

    $this->actingAs($staff);

    $response = $this->postJson(route('patients.medical-history.medications.store', ['patientUuid' => $patientUuid]), [
        'medication_id' => $medication->id,
        'dosage' => '1 tablet',
        'frequency' => 'twice daily',
        'start_date' => '2024-05-01',
        'end_date' => '2024-05-10',
        'notes' => 'Take with food',
    ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('medication_histories', [
        'user_id' => $patient->id,
        'medication_id' => $medication->id,
        'dosage' => '1 tablet',
        'frequency' => 'twice daily',
        'start_date' => '2024-05-01',
        'end_date' => '2024-05-10',
        'notes' => 'Take with food',
    ]);

    $hasEvent = StoredEvent::query()
        ->where('aggregate_type', 'patient')
        ->where('event_type', 'patient.medication_added')
        ->where('event_data->user_id', $patient->id)
        ->where('event_data->medication_id', $medication->id)
        ->exists();

    expect($hasEvent)->toBeTrue();
});

it('records a visit summary for a patient via staff endpoint', function () {
    $staff = User::factory()->create();
    $staff->role = 'staff';

    $patient = User::factory()->create();
    $patient->role = 'patient';

    $patientUuid = (string) Str::uuid();

    PatientEnrollment::create([
        'patient_uuid' => $patientUuid,
        'user_id' => $patient->id,
        'source' => 'manual',
        'metadata' => null,
        'enrolled_at' => now(),
    ]);

    $this->actingAs($staff);

    $response = $this->postJson(route('patients.medical-history.visit-summary.store', ['patientUuid' => $patientUuid]), [
        'past_injuries' => true,
        'past_injuries_details' => 'Fractured arm in 2010',
        'surgery' => true,
        'surgery_details' => 'Appendectomy in 2015',
        'chronic_conditions_details' => 'Hypertension and diabetes',
        'chronic_pain' => true,
        'chronic_pain_details' => 'Chronic lower back pain',
        'family_history_conditions' => ['Diabetes', 'Heart disease'],
    ]);

    $response->assertOk();

    $this->assertDatabaseHas('medical_surgical_histories', [
        'patient_id' => $patient->id,
        'past_injuries' => 1,
        'past_injuries_details' => 'Fractured arm in 2010',
        'surgery' => 1,
        'surgery_details' => 'Appendectomy in 2015',
        'chronic_conditions_details' => 'Hypertension and diabetes',
    ]);

    $this->assertDatabaseHas('family_medical_histories', [
        'patient_id' => $patient->id,
        'chronic_pain' => 1,
        'chronic_pain_details' => 'Chronic lower back pain',
    ]);

    $familyHistory = FamilyMedicalHistory::where('patient_id', $patient->id)->firstOrFail();

    $this->assertDatabaseHas('family_medical_conditions', [
        'family_medical_history_id' => $familyHistory->id,
        'name' => 'Diabetes',
    ]);

    $this->assertDatabaseHas('family_medical_conditions', [
        'family_medical_history_id' => $familyHistory->id,
        'name' => 'Heart disease',
    ]);

    $hasEvent = StoredEvent::query()
        ->where('aggregate_type', 'patient')
        ->where('event_type', 'patient.visit_summary_recorded')
        ->where('event_data->patient_id', $patient->id)
        ->exists();

    expect($hasEvent)->toBeTrue();

    $response->assertJsonPath('medical_history.surgical_history.past_injuries', true);
});

