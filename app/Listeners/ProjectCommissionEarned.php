<?php

namespace App\Listeners;

use App\Application\Commission\CommissionDashboardProjector;
use App\Domain\Commission\Events\CommissionEarned;

class ProjectCommissionEarned
{
    public function __construct(
        private CommissionDashboardProjector $projector,
    ) {
    }

    public function handle(CommissionEarned $event): void
    {
        $this->projector->projectCommissionEarned($event);
    }
}

