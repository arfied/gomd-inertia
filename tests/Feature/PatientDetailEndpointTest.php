<?php

use App\Models\Allergy;
use App\Models\FamilyMedicalCondition;
use App\Models\FamilyMedicalHistory;
use App\Models\MedicalCondition;
use App\Models\MedicalSurgicalHistory;
use App\Models\Medication;
use App\Models\MedicationHistory;
use App\Models\PatientEnrollment;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Support\Str;

it('returns unauthorized for guests', function () {
    $this->getJson(route('patients.show', ['patientUuid' => (string) Str::uuid()]))
        ->assertStatus(401);
});

it('forbids non-staff users from accessing patient detail endpoint', function () {
    $user = User::factory()->create();
    $user->role = 'patient';

    $this->actingAs($user);

    $this->getJson(route('patients.show', ['patientUuid' => (string) Str::uuid()]))
        ->assertStatus(403);
});

it('returns 404 when the patient enrollment cannot be found', function () {
    $staff = User::factory()->create();
    $staff->role = 'staff';

    $this->actingAs($staff);

    $this->getJson(route('patients.show', ['patientUuid' => (string) Str::uuid()]))
        ->assertStatus(404);
});

it('returns patient detail for staff users', function () {
    $staff = User::factory()->create();
    $staff->role = 'staff';

    /** @var User $patient */
    $patient = User::factory()->create([
        'fname' => 'Alice',
        'lname' => 'Wonder',
        'email' => 'alice@example.test',
        'gender' => 'female',
        'dob' => '1990-01-02',
        'address1' => '123 Main St',
        'city' => 'Metropolis',
        'state' => 'NY',
        'zip' => '10001',
        'phone' => '555-0000',
        'mobile_phone' => '555-0001',
    ]);
    $patient->role = 'patient';

    $patientUuid = (string) Str::uuid();

    $enrollment = PatientEnrollment::create([
        'patient_uuid' => $patientUuid,
        'user_id' => $patient->id,
        'source' => 'manual',
        'metadata' => ['foo' => 'bar'],
        'enrolled_at' => now()->subDay(),
    ]);

    $plan = SubscriptionPlan::create([
        'name' => 'TeleMed Pro Monthly',
        'price' => 30.00,
        'duration_months' => 1,
        'service_limit' => null,
        'status' => 'active',
    ]);

    $subscription = Subscription::create([
        'user_id' => $patient->id,
        'plan_id' => $plan->id,
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addMonth(),
        'status' => Subscription::STATUS_ACTIVE,
        'is_trial' => false,
    ]);

    $this->actingAs($staff);

    $response = $this->getJson(route('patients.show', ['patientUuid' => $patientUuid]));

    $response
        ->assertOk()
        ->assertJson([
            'patient' => [
                'patient_uuid' => $enrollment->patient_uuid,
                'user_id' => $patient->id,
                'fname' => 'Alice',
                'lname' => 'Wonder',
                'email' => 'alice@example.test',
                'enrollment' => [
                    'source' => 'manual',
                    'metadata' => ['foo' => 'bar'],
                ],
                'subscription' => [
                    'id' => $subscription->id,
                    'status' => Subscription::STATUS_ACTIVE,
                    'plan_name' => 'TeleMed Pro Monthly',
                    'is_trial' => false,
                ],
            ],
        ])
        ->assertJsonStructure([
            'patient' => [
                'patient_uuid',
                'user_id',
                'fname',
                'lname',
                'email',
                'status',
                'demographics' => [
                    'gender',
                    'dob',
                    'address1',
                    'address2',
                    'city',
                    'state',
                    'zip',
                    'phone',
                    'mobile_phone',
                ],
                'enrollment' => [
                    'source',
                    'metadata',
                    'enrolled_at',
                ],
                'subscription' => [
                    'id',
                    'status',
                    'plan_name',
                    'is_trial',
                    'starts_at',
                    'ends_at',
                ],
                'medical_history' => [
                    'allergies',
                    'conditions',
                    'medications',
                    'surgical_history',
                    'family_history',
                ],
            ],
        ]);
});

it('includes medical history snapshot in patient detail response', function () {
    $staff = User::factory()->create();
    $staff->role = 'staff';

    /** @var User $patient */
    $patient = User::factory()->create([
        'fname' => 'Bob',
        'lname' => 'Builder',
        'email' => 'bob@example.test',
    ]);
    $patient->role = 'patient';

    $patientUuid = (string) Str::uuid();

    $enrollment = PatientEnrollment::create([
        'patient_uuid' => $patientUuid,
        'user_id' => $patient->id,
        'source' => 'manual',
        'metadata' => null,
        'enrolled_at' => now()->subDays(2),
    ]);

    Allergy::create([
        'user_id' => $patient->id,
        'allergen' => 'Peanuts',
        'reaction' => 'Anaphylaxis',
        'severity' => Allergy::SEVERITY_SEVERE,
        'notes' => 'Carries EpiPen',
    ]);

    MedicalCondition::create([
        'patient_id' => $patient->id,
        'condition_name' => 'Hypertension',
        'diagnosed_at' => '2020-01-01',
        'notes' => 'Controlled with medication',
        'had_condition_before' => false,
        'is_chronic' => true,
    ]);

    MedicalSurgicalHistory::create([
        'patient_id' => $patient->id,
        'past_injuries' => true,
        'past_injuries_details' => 'Fractured arm in 2010',
        'surgery' => true,
        'surgery_details' => 'Appendectomy in 2015',
        'chronic_conditions_details' => 'Chronic lower back pain',
    ]);

    $familyHistory = FamilyMedicalHistory::create([
        'patient_id' => $patient->id,
        'chronic_pain' => true,
        'chronic_pain_details' => 'Mother with chronic back pain',
    ]);

    FamilyMedicalCondition::create([
        'family_medical_history_id' => $familyHistory->id,
        'name' => 'Diabetes',
    ]);

    $medication = Medication::create([
        'name' => 'Ibuprofen',
        'generic_name' => 'Ibuprofen',
        'description' => 'Pain reliever',
        'dosage_form' => 'tablet',
        'strength' => '200mg',
        'manufacturer' => 'Acme Pharma',
        'ndc_number' => '12345-6789',
        'unit_price' => 10.00,
        'requires_prescription' => false,
        'controlled_substance' => false,
        'storage_conditions' => null,
    ]);

    $history = new MedicationHistory();
    $history->user_id = $patient->id;
    $history->medication_id = $medication->id;
    $history->start_date = now()->subDays(10)->toDateString();
    $history->end_date = null;
    $history->dosage = '1 tablet';
    $history->frequency = 'twice daily';
    $history->notes = 'After meals';
    $history->save();

    $this->actingAs($staff);

    $response = $this->getJson(route('patients.show', ['patientUuid' => $patientUuid]));

    $response->assertOk();

    $response->assertJsonPath('patient.medical_history.allergies.0.allergen', 'Peanuts');
    $response->assertJsonPath('patient.medical_history.conditions.0.condition_name', 'Hypertension');
    $response->assertJsonPath('patient.medical_history.medications.0.dosage', '1 tablet');
    $response->assertJsonPath('patient.medical_history.surgical_history.past_injuries', true);
    $response->assertJsonPath('patient.medical_history.family_history.chronic_pain', true);
});


