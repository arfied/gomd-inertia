<?php

namespace App\Application\Order;

use App\Domain\Order\Events\OrderCreated;

interface OrderProjector
{
    public function projectOrderCreated(OrderCreated $event): void;
}

