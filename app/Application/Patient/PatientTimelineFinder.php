<?php

namespace App\Application\Patient;

use App\Models\StoredEvent;
use Illuminate\Support\Collection;

interface PatientTimelineFinder
{
    /**
     * Find a chronological list of patient-related events for a given user.
     *
     * @param  int  $userId
     * @param  int  $limit
     * @param  string|null  $filter
     * @return Collection<int, StoredEvent>
     */
    public function findTimelineByUserId(int $userId, int $limit = 50, ?string $filter = null): Collection;
}

