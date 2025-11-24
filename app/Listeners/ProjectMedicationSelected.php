<?php

namespace App\Listeners;

use App\Domain\Signup\Events\MedicationSelected;
use App\Models\SignupReadModel;

class ProjectMedicationSelected
{
    public function handle(MedicationSelected $event): void
    {
        $signup = SignupReadModel::where('signup_uuid', $event->aggregateUuid)->first();

        if ($signup) {
            // Get existing medications or start with empty array
            $medications = $signup->medication_name ?? [];

            // Add new medication if not already present
            if (!in_array($event->medicationName, $medications)) {
                $medications[] = $event->medicationName;
            }

            $signup->update([
                'medication_name' => $medications,
                'updated_at' => $event->occurredAt,
            ]);
        }
    }
}

