<?php

namespace App\Application\Prescription;

use App\Domain\Prescription\Events\PrescriptionCreated;
use App\Models\MedicationOrder;
use App\Models\Prescription;

class EloquentPrescriptionProjector implements PrescriptionProjector
{
    public function projectPrescriptionCreated(PrescriptionCreated $event): void
    {
        $payload = $event->payload;

        $patientId = $payload['user_id'] ?? null;

        if ($patientId === null) {
            return;
        }

        $prescription = Prescription::create([
            'user_id' => $patientId,
            'doctor_id' => $payload['doctor_id'] ?? null,
            'pharmacist_id' => $payload['pharmacist_id'] ?? null,
            'status' => $payload['status'] ?? 'pending',
            'notes' => $payload['notes'] ?? null,
            'is_non_standard' => $payload['is_non_standard'] ?? false,
        ]);

        $orderId = $event->metadata['order_id'] ?? null;

        if ($orderId === null) {
            return;
        }

        /** @var MedicationOrder|null $order */
        $order = MedicationOrder::find($orderId);

        if (! $order instanceof MedicationOrder) {
            return;
        }

        // Guard against linking a prescription to an order for a different patient.
        if ((int) $order->patient_id !== (int) $patientId) {
            return;
        }

        $order->linkPrescription($prescription);
    }
}

