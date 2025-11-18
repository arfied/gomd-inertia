<?php

namespace App\Application\Order\Commands;

use App\Domain\Shared\Commands\Command;

/**
 * InitiateShipment Command
 *
 * Part of the order fulfillment saga.
 * Triggered after inventory is reserved.
 * Initiates shipment of the order to the patient.
 */
class InitiateShipment implements Command
{
    public function __construct(
        public string $orderUuid,
        public string $sagaUuid,
        public string $shippingAddress,
        public ?string $shippingMethod = null,
        public ?string $trackingNumber = null,
        public array $metadata = [],
    ) {
    }
}

