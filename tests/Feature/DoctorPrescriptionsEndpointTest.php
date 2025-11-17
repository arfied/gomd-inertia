<?php

use App\Models\Medication;
use App\Models\MedicationOrder;
use App\Models\MedicationOrderItem;
use App\Models\PatientEnrollment;
use App\Models\User;
use Illuminate\Support\Str;

it('requires authentication and doctor role for prescription creation', function () {
    $patientUuid = (string) Str::uuid();

    $this->postJson(route('patients.orders.prescriptions.store', ['patientUuid' => $patientUuid, 'order' => 1]))
        ->assertStatus(401);

    /** @var User $user */
    $user = User::factory()->create();
    $user->role = 'patient';

    $this->actingAs($user);

    $this->postJson(route('patients.orders.prescriptions.store', ['patientUuid' => $patientUuid, 'order' => 1]))
        ->assertStatus(403);
});

it('creates a prescription for a patient order via doctor endpoint', function () {
    /** @var User $doctor */
    $doctor = User::factory()->create();
    $doctor->role = 'doctor';

    /** @var User $patient */
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

    /** @var Medication $medication */
    $medication = Medication::create([
        'name' => 'Amoxicillin',
        'dosage_form' => 'tablet',
        'strength' => '500mg',
    ]);

    /** @var MedicationOrder $order */
    $order = MedicationOrder::create([
        'patient_id' => $patient->id,
        'doctor_id' => null,
        'prescription_id' => null,
        'status' => MedicationOrder::STATUS_PENDING,
        'patient_notes' => 'Take with food',
        'doctor_notes' => null,
        'rejection_reason' => null,
        'assigned_at' => null,
        'prescribed_at' => null,
        'completed_at' => null,
        'rejected_at' => null,
    ]);

    MedicationOrderItem::create([
        'medication_order_id' => $order->id,
        'medication_id' => $medication->id,
        'custom_medication_name' => null,
        'custom_medication_details' => null,
        'requested_dosage' => '500mg',
        'requested_quantity' => 30,
        'status' => MedicationOrderItem::STATUS_PENDING,
        'rejection_reason' => null,
    ]);

    $this->actingAs($doctor);

    $response = $this->postJson(
        route('patients.orders.prescriptions.store', ['patientUuid' => $patientUuid, 'order' => $order->id]),
        [
            'notes' => 'Take with food',
            'is_non_standard' => true,
        ],
    );

    $response
        ->assertStatus(201)
        ->assertJsonPath('prescription.user_id', $patient->id)
        ->assertJsonPath('prescription.doctor_id', $doctor->id)
        ->assertJsonPath('prescription.notes', 'Take with food')
        ->assertJsonPath('prescription.is_non_standard', true);

    $this->assertDatabaseHas('prescriptions', [
        'user_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'notes' => 'Take with food',
        'is_non_standard' => true,
    ]);
});

