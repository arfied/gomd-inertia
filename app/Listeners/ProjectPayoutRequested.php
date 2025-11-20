<?php

namespace App\Listeners;

use App\Application\Commission\PayoutHistoryProjector;
use App\Domain\Commission\Events\PayoutRequested;

class ProjectPayoutRequested
{
    public function __construct(
        private PayoutHistoryProjector $projector,
    ) {
    }

    public function handle(PayoutRequested $event): void
    {
        $this->projector->projectPayoutRequested($event);
    }
}

