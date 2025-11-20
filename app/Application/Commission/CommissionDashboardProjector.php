<?php

namespace App\Application\Commission;

use App\Domain\Commission\Events\CommissionCancelled;
use App\Domain\Commission\Events\CommissionEarned;

/**
 * Interface for Commission Dashboard projector.
 *
 * Handles projections of commission events into read models
 * optimized for dashboard queries and analytics.
 */
interface CommissionDashboardProjector
{
    public function projectCommissionEarned(CommissionEarned $event): void;

    public function projectCommissionCancelled(CommissionCancelled $event): void;
}

