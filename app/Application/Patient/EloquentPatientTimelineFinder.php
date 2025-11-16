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

    public function findTimelineByUserId(int $userId, int $limit = 50, ?string $filter = null): Collection
    {
        $enrollment = $this->enrollmentFinder->findByUserId($userId);

        if ($enrollment === null) {
            return collect();
        }

        $query = StoredEvent::query()
            ->where('aggregate_uuid', $enrollment->patient_uuid);

        if ($filter === 'enrollment') {
            $query->where('event_type', 'patient.enrolled');
        } elseif ($filter === 'other') {
            $query->where('event_type', '!=', 'patient.enrolled');
        }

        return $query
            ->orderBy('occurred_at')
            ->limit($limit)
            ->get();
    }
}

