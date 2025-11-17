<?php

namespace App\Listeners;

use App\Application\Order\OrderProjector;
use App\Domain\Order\Events\OrderCreated;

class ProjectOrderCreated
{
    public function __construct(
        private OrderProjector $projector,
    ) {
    }

    public function handle(OrderCreated $event): void
    {
        $this->projector->projectOrderCreated($event);
    }
}

