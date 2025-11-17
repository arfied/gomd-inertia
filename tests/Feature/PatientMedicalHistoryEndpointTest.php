<?php

use App\Models\Allergy;
use App\Models\FamilyMedicalCondition;
use App\Models\FamilyMedicalHistory;
use App\Models\MedicalSurgicalHistory;
use App\Models\PatientEnrollment;
use App\Models\StoredEvent;
use App\Models\User;

it('returns unauthorized for guests', function () {
    $this->getJson(route('patient.medical-history.show'))
        ->assertStatus(401);

    $this->postJson(route('patient.medical-history.allergies.store'))
        ->assertStatus(401);

    $this->postJson(route('patient.medical-history.visit-summary.store'))
        ->assertStatus(401);
});

it('returns a medical history snapshot for the authenticated user', function () {
    /** @var User $user */
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->getJson(route('patient.medical-history.show'));

    $response
        ->assertOk()
        ->assertJsonStructure([
            'medical_history' => [
                'allergies',
                'conditions',
                'medications',
                'surgical_history',
                'family_history',
            ],
        ]);
});

it('rejects medical history writes when the patient is not enrolled', function () {
    /** @var User $user */
    $user = User::factory()->create();

    $this->actingAs($user);

    $this->postJson(route('patient.medical-history.allergies.store'), [
        'allergen' => 'Peanuts',
    ])
        ->assertStatus(422)
        ->assertJson([
            'message' => 'Patient is not enrolled.',
        ]);
});

it('records an allergy for the authenticated patient via patient endpoint', function () {
    /** @var User $user */
    $user = User::factory()->create();

    $this->actingAs($user);

    // Enroll the patient via the existing endpoint so we have a patient_uuid
    $this->postJson(route('patient.enrollment.store'))
        ->assertCreated();

    $response = $this->postJson(route('patient.medical-history.allergies.store'), [
        'allergen' => 'Peanuts',
        'reaction' => 'Anaphylaxis',
        'severity' => 'severe',
        'notes' => 'Carries EpiPen',
    ]);

    $response
        ->assertStatus(201)
        ->assertJsonPath('medical_history.allergies.0.allergen', 'Peanuts');

    $this->assertDatabaseHas('allergies', [
        'user_id' => $user->id,
        'allergen' => 'Peanuts',
        'reaction' => 'Anaphylaxis',
        'severity' => 'severe',
        'notes' => 'Carries EpiPen',
    ]);

    $hasEvent = StoredEvent::query()
        ->where('aggregate_type', 'patient')
        ->where('event_type', 'patient.allergy_recorded')
        ->where('event_data->user_id', $user->id)
        ->where('event_data->allergen', 'Peanuts')
        ->exists();

    expect($hasEvent)->toBeTrue();
});

it('records a visit summary for the authenticated patient via patient endpoint', function () {
    /** @var User $user */
    $user = User::factory()->create();

    $this->actingAs($user);

    // Enroll the patient so we have a patient_uuid
    $this->postJson(route('patient.enrollment.store'))
        ->assertCreated();

    $payload = [
        'past_injuries' => true,
        'past_injuries_details' => 'Fractured arm in 2010',
        'surgery' => true,
        'surgery_details' => 'Appendectomy in 2015',
        'chronic_conditions_details' => 'Hypertension and diabetes',
        'chronic_pain' => true,
        'chronic_pain_details' => 'Chronic lower back pain',
        'family_history_conditions' => ['Diabetes', 'Heart disease'],
    ];

    $response = $this->postJson(route('patient.medical-history.visit-summary.store'), $payload);

    $response
        ->assertOk()
        ->assertJsonPath('medical_history.surgical_history.past_injuries', true);

    /** @var PatientEnrollment $enrollment */
    $enrollment = PatientEnrollment::query()->where('user_id', $user->id)->firstOrFail();

    $this->assertDatabaseHas('medical_surgical_histories', [
        'patient_id' => $enrollment->user_id,
        'past_injuries' => 1,
        'past_injuries_details' => 'Fractured arm in 2010',
        'surgery' => 1,
        'surgery_details' => 'Appendectomy in 2015',
        'chronic_conditions_details' => 'Hypertension and diabetes',
    ]);

    $this->assertDatabaseHas('family_medical_histories', [
        'patient_id' => $enrollment->user_id,
        'chronic_pain' => 1,
        'chronic_pain_details' => 'Chronic lower back pain',
    ]);

    $familyHistory = FamilyMedicalHistory::where('patient_id', $enrollment->user_id)->firstOrFail();

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
        ->where('event_data->patient_id', $enrollment->user_id)
        ->exists();

    expect($hasEvent)->toBeTrue();
});

