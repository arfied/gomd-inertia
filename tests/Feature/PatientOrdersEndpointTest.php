<?php

use App\Models\Medication;
use App\Models\MedicationOrder;
use App\Models\MedicationOrderItem;
use App\Models\User;

it('returns unauthorized for guests', function () {
    $response = $this->getJson(route('patient.orders.index'));

    $response->assertStatus(401);
});

it('returns orders for the authenticated user based on existing medication_orders', function () {
    /** @var User $user */
    $user = User::factory()->create();

    /** @var Medication $medication */
    $medication = Medication::create([
        'name' => 'Amoxicillin',
        'dosage_form' => 'tablet',
        'strength' => '500mg',
    ]);

    /** @var MedicationOrder $order */
    $order = MedicationOrder::create([
        'patient_id' => $user->id,
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

    $this->actingAs($user);

    $response = $this->getJson(route('patient.orders.index'));

    $response
        ->assertOk()
        ->assertJsonCount(1, 'orders')
        ->assertJsonPath('orders.0.patient_id', $user->id)
        ->assertJsonPath('orders.0.status', MedicationOrder::STATUS_PENDING)
        ->assertJsonPath('orders.0.items.0.medication_id', $medication->id)
        ->assertJsonPath('orders.0.items.0.medication_name', 'Amoxicillin')
        ->assertJsonPath('orders.0.items.0.requested_dosage', '500mg')
        ->assertJsonPath('orders.0.items.0.requested_quantity', 30);
});

