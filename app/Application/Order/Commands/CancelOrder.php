<?php

namespace App\Application\Order\Commands;

use App\Domain\Shared\Commands\Command;

/**
 * Command DTO for cancelling an order.
 */
class CancelOrder implements Command
{
    public function __construct(
        public string $orderUuid,
        public ?string $reason = null,
        public array $metadata = [],
    ) {
    }
}

