<?php

namespace App\Application\Patient;

use App\Models\Subscription;

class EloquentPatientSubscriptionFinder implements PatientSubscriptionFinder
{
    public function findCurrentByUserId(int $userId): ?Subscription
    {
        return Subscription::query()
            ->where('user_id', $userId)
            ->orderByDesc('id')
            ->first();
    }
}

