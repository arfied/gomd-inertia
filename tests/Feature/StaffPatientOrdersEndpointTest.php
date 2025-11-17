<?php

use App\Models\Medication;
use App\Models\MedicationOrder;
use App\Models\MedicationOrderItem;
use App\Models\PatientEnrollment;
use App\Models\User;
use Illuminate\Support\Str;

it('requires authentication and staff role for patient orders endpoints', function () {
    $patientUuid = (string) Str::uuid();

    $this->getJson(route('patients.orders.index', ['patientUuid' => $patientUuid]))
        ->assertStatus(401);

    /** @var User $user */
    $user = User::factory()->create();
    $user->role = 'patient';

    $this->actingAs($user);

    $this->getJson(route('patients.orders.index', ['patientUuid' => $patientUuid]))
        ->assertStatus(403);
});

it('returns orders for a patient by patient uuid for staff', function () {
    /** @var User $staff */
    $staff = User::factory()->create();
    $staff->role = 'staff';

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

    $this->actingAs($staff);

    $response = $this->getJson(route('patients.orders.index', ['patientUuid' => $patientUuid]));

    $response
        ->assertOk()
        ->assertJsonCount(1, 'orders')
        ->assertJsonPath('orders.0.patient_id', $patient->id)
        ->assertJsonPath('orders.0.items.0.medication_id', $medication->id)
        ->assertJsonPath('orders.0.items.0.medication_name', 'Amoxicillin')
        ->assertJsonPath('orders.0.items.0.requested_dosage', '500mg')
        ->assertJsonPath('orders.0.items.0.requested_quantity', 30);
});

