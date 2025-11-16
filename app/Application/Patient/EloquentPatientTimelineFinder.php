<?php

namespace App\Application\Patient;

use App\Models\StoredEvent;
use Illuminate\Support\Collection;

class EloquentPatientTimelineFinder implements PatientTimelineFinder
{
    public function __construct(
        private PatientEnrollmentFinder $enrollmentFinder,
    ) {
    }

    public function findTimelineByUserId(int $userId, int $limit = 50): Collection
    {
        $enrollment = $this->enrollmentFinder->findByUserId($userId);

        if ($enrollment === null) {
            return collect();
        }

        return StoredEvent::query()
            ->where('aggregate_uuid', $enrollment->patient_uuid)
            ->orderBy('occurred_at')
            ->limit($limit)
            ->get();
    }
}

