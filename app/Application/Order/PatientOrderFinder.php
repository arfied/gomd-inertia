<?php

namespace App\Application\Order;

use App\Models\MedicationOrder;
use Illuminate\Support\Collection;

interface PatientOrderFinder
{
    /**
     * @return Collection<int, MedicationOrder>
     */
    public function findByUserId(int $userId): Collection;

    /**
     * @return Collection<int, MedicationOrder>
     */
    public function findByPatientUuid(string $patientUuid): Collection;
}

