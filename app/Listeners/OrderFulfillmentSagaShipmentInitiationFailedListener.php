<?php

namespace App\Listeners;

use App\Domain\Order\Events\ShipmentInitiationFailed;
use App\Jobs\Order\ReleaseInventoryJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * OrderFulfillmentSagaShipmentInitiationFailedListener
 *
 * Listens to ShipmentInitiationFailed event and triggers full compensation.
 * Compensation chain: Release Inventory → Cancel Prescription → Cancel Order
 */
class OrderFulfillmentSagaShipmentInitiationFailedListener implements ShouldQueue
{
    use Queueable;

    /**
     * Handle the ShipmentInitiationFailed event.
     *
     * @param ShipmentInitiationFailed $event
     * @return void
     */
    public function handle(ShipmentInitiationFailed $event): void
    {
        dispatch(new ReleaseInventoryJob(
            $event->aggregateUuid,
            ['reason' => 'Shipment initiation failed', ...$event->payload]
        ))->onQueue('order-fulfillment');
    }
}

