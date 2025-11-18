<?php

namespace App\Listeners;

use App\Domain\Order\Events\ShipmentInitiated;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * OrderFulfillmentSagaShipmentInitiatedListener
 *
 * Listens to ShipmentInitiated event and marks saga as complete.
 * This is the final step in the order fulfillment saga (happy path).
 */
class OrderFulfillmentSagaShipmentInitiatedListener implements ShouldQueue
{
    use Queueable;

    /**
     * Handle the ShipmentInitiated event.
     *
     * @param ShipmentInitiated $event
     * @return void
     */
    public function handle(ShipmentInitiated $event): void
    {
        // Saga completes successfully
        // TODO: Dispatch any post-fulfillment actions (notifications, analytics, etc.)
        // Example:
        // dispatch(new SendOrderConfirmationEmailJob($event->aggregateUuid));
        // dispatch(new UpdateOrderAnalyticsJob($event->aggregateUuid));
    }
}

