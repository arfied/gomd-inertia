<?php

namespace App\Application\Patient;

use App\Models\Activity;
use Illuminate\Support\Collection;

class EloquentPatientActivityFinder implements PatientActivityFinder
{
    public function findRecentByUserId(int $userId, int $limit = 5): Collection
    {
        return Activity::query()
            ->where('user_id', $userId)
            ->where('type', 'like', 'patient.%')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}

