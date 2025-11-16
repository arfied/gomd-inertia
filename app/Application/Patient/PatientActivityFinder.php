<?php

namespace App\Application\Patient;

use App\Models\Activity;
use Illuminate\Support\Collection;

interface PatientActivityFinder
{
    /**
     * Find recent patient-related activities for the given user.
     *
     * @return Collection<int, Activity>
     */
    public function findRecentByUserId(int $userId, int $limit = 5): Collection;
}

