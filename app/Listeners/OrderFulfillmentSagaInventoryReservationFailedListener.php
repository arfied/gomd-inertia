<?php

namespace App\Listeners;

use App\Domain\Order\Events\InventoryReservationFailed;
use App\Jobs\Order\CancelPrescriptionJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * OrderFulfillmentSagaInventoryReservationFailedListener
 *
 * Listens to InventoryReservationFailed event and triggers compensation.
 * Compensation: Cancel prescription (which will trigger cancel order).
 */
class OrderFulfillmentSagaInventoryReservationFailedListener implements ShouldQueue
{
    use Queueable;

    /**
     * Handle the InventoryReservationFailed event.
     *
     * @param InventoryReservationFailed $event
     * @return void
     */
    public function handle(InventoryReservationFailed $event): void
    {
        dispatch(new CancelPrescriptionJob(
            $event->aggregateUuid,
            ['reason' => 'Inventory reservation failed', ...$event->payload]
        ))->onQueue('order-fulfillment');
    }
}

