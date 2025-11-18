<?php

namespace App\Application\Order\Commands;

use App\Domain\Shared\Commands\Command;

/**
 * ReserveInventory Command
 *
 * Part of the order fulfillment saga.
 * Triggered after prescription is created.
 * Reserves inventory for the prescribed medications.
 */
class ReserveInventory implements Command
{
    public function __construct(
        public string $orderUuid,
        public string $sagaUuid,
        public array $medications,
        public ?string $warehouseId = null,
        public array $metadata = [],
    ) {
    }
}

