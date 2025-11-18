<?php

namespace App\Listeners;

use App\Domain\Order\Events\InventoryReserved;
use App\Jobs\Order\InitiateShipmentJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * OrderFulfillmentSagaInventoryReservedListener
 *
 * Listens to InventoryReserved event and dispatches InitiateShipmentJob.
 * This is the third step in the order fulfillment saga.
 */
class OrderFulfillmentSagaInventoryReservedListener implements ShouldQueue
{
    use Queueable;

    /**
     * Handle the InventoryReserved event.
     *
     * @param InventoryReserved $event
     * @return void
     */
    public function handle(InventoryReserved $event): void
    {
        dispatch(new InitiateShipmentJob(
            $event->aggregateUuid,
            $event->payload
        ))->onQueue('order-fulfillment');
    }
}

