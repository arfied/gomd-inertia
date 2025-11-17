<?php

namespace App\Application\Order;

use App\Domain\Order\Events\OrderCreated;
use App\Models\MedicationOrder;

class EloquentOrderProjector implements OrderProjector
{
    public function projectOrderCreated(OrderCreated $event): void
    {
        $payload = $event->payload;

        $patientId = $payload['patient_id'] ?? null;

        if ($patientId === null) {
            return;
        }

        MedicationOrder::create([
            'patient_id' => $patientId,
            'doctor_id' => $payload['doctor_id'] ?? null,
            'prescription_id' => $payload['prescription_id'] ?? null,
            'status' => $payload['status'] ?? MedicationOrder::STATUS_PENDING,
            'patient_notes' => $payload['patient_notes'] ?? null,
            'doctor_notes' => $payload['doctor_notes'] ?? null,
        ]);
    }
}

