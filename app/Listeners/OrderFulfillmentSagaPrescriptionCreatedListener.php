<?php

namespace App\Listeners;

use App\Domain\Order\Events\PrescriptionCreated;
use App\Jobs\Order\ReserveInventoryJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * OrderFulfillmentSagaPrescriptionCreatedListener
 *
 * Listens to PrescriptionCreated event and dispatches ReserveInventoryJob.
 * This is the second step in the order fulfillment saga.
 */
class OrderFulfillmentSagaPrescriptionCreatedListener implements ShouldQueue
{
    use Queueable;

    /**
     * Handle the PrescriptionCreated event.
     *
     * @param PrescriptionCreated $event
     * @return void
     */
    public function handle(PrescriptionCreated $event): void
    {
        dispatch(new ReserveInventoryJob(
            $event->aggregateUuid,
            $event->payload
        ))->onQueue('order-fulfillment');
    }
}

