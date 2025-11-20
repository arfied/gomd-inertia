<?php

namespace App\Listeners;

use App\Application\Commission\CommissionDashboardProjector;
use App\Domain\Commission\Events\CommissionCancelled;

class ProjectCommissionCancelled
{
    public function __construct(
        private CommissionDashboardProjector $projector,
    ) {
    }

    public function handle(CommissionCancelled $event): void
    {
        $this->projector->projectCommissionCancelled($event);
    }
}

