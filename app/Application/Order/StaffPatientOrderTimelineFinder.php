<?php

namespace App\Application\Order;

use App\Models\StoredEvent;
use Illuminate\Support\Collection;

interface StaffPatientOrderTimelineFinder
{
    /**
     * Find a chronological list of order-related events for a given patient UUID.
     *
     * @param  string  $patientUuid
     * @param  int  $limit
     * @param  string|null  $filter
     * @return Collection<int, StoredEvent>
     */
    public function findTimelineByPatientUuid(string $patientUuid, int $limit = 50, ?string $filter = null): Collection;
}

