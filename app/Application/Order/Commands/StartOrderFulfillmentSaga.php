<?php

namespace App\Application\Order\Commands;

use App\Domain\Shared\Commands\Command;

/**
 * StartOrderFulfillmentSaga Command
 *
 * Initiates the order fulfillment saga.
 * Triggered when an order is created.
 */
class StartOrderFulfillmentSaga implements Command
{
    public function __construct(
        public string $sagaUuid,
        public string $orderUuid,
        public array $metadata = [],
    ) {
    }
}

