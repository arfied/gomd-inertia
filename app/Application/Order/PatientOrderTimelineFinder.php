<?php

namespace App\Application\Order;

use App\Models\StoredEvent;
use Illuminate\Support\Collection;

interface PatientOrderTimelineFinder
{
    /**
     * Find a chronological list of order-related events for a given user.
     *
     * @param  int  $userId
     * @param  int  $limit
     * @param  string|null  $filter
     * @return Collection<int, StoredEvent>
     */
    public function findTimelineByUserId(int $userId, int $limit = 50, ?string $filter = null): Collection;
}

