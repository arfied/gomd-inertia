<?php

namespace App\Application\Order;

use App\Models\MedicationOrder;
use App\Models\StoredEvent;
use Illuminate\Support\Collection;

class EloquentPatientOrderTimelineFinder implements PatientOrderTimelineFinder
{
    public function findTimelineByUserId(int $userId, int $limit = 50, ?string $filter = null): Collection
    {
        // Get all order UUIDs for this user
        $orderUuids = MedicationOrder::query()
            ->where('patient_id', $userId)
            ->pluck('id')
            ->toArray();

        if (empty($orderUuids)) {
            return collect();
        }

        $query = StoredEvent::query()
            ->whereIn('aggregate_uuid', $orderUuids)
            ->where('aggregate_type', 'order');

        // Apply filter if provided
        if ($filter === 'created') {
            $query->where('event_type', 'order.created');
        } elseif ($filter === 'prescribed') {
            $query->where('event_type', 'order.prescription_created');
        } elseif ($filter === 'fulfilled') {
            $query->where('event_type', 'order.fulfilled');
        } elseif ($filter === 'cancelled') {
            $query->where('event_type', 'order.cancelled');
        }

        return $query
            ->orderBy('occurred_at', 'desc')
            ->limit($limit)
            ->get();
    }
}

