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
            $signup->update([
                'medication_name' => $event->medicationName,
                'updated_at' => $event->occurredAt,
            ]);
        }
    }
}

