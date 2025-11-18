<?php

namespace App\Listeners;

use App\Domain\Order\Events\OrderCreated;
use App\Jobs\Order\CreatePrescriptionJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * OrderFulfillmentSagaOrderCreatedListener
 *
 * Listens to OrderCreated event and dispatches CreatePrescriptionJob.
 * This is the first step in the order fulfillment saga.
 *
 * Laravel 12 automatically discovers this listener because:
 * - It's in app/Listeners directory
 * - It has a handle() method
 * - The handle() method type-hints OrderCreated event
 */
class OrderFulfillmentSagaOrderCreatedListener implements ShouldQueue
{
    use Queueable;

    /**
     * Handle the OrderCreated event.
     *
     * @param OrderCreated $event
     * @return void
     */
    public function handle(OrderCreated $event): void
    {
        dispatch(new CreatePrescriptionJob(
            $event->aggregateUuid,
            $event->payload
        ))->onQueue('order-fulfillment');
    }
}

