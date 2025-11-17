<?php

namespace App\Application\Order\Commands;

use App\Domain\Shared\Commands\Command;

/**
 * Command DTO for fulfilling an order.
 */
class FulfillOrder implements Command
{
    public function __construct(
        public string $orderUuid,
        public array $metadata = [],
    ) {
    }
}

