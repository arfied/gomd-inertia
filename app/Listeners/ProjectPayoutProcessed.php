<?php

namespace App\Listeners;

use App\Application\Commission\PayoutHistoryProjector;
use App\Domain\Commission\Events\PayoutProcessed;

class ProjectPayoutProcessed
{
    public function __construct(
        private PayoutHistoryProjector $projector,
    ) {
    }

    public function handle(PayoutProcessed $event): void
    {
        $this->projector->projectPayoutProcessed($event);
    }
}

