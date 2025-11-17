<?php

namespace App\Application\Patient;

use App\Models\Subscription;

interface PatientSubscriptionFinder
{
    public function findCurrentByUserId(int $userId): ?Subscription;
}

