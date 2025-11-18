<?php

namespace App\Listeners;

use App\Domain\Order\Events\PrescriptionFailed;
use App\Jobs\Order\CancelOrderJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * OrderFulfillmentSagaPrescriptionFailedListener
 *
 * Listens to PrescriptionFailed event and triggers compensation.
 * Compensation: Cancel the order.
 */
class OrderFulfillmentSagaPrescriptionFailedListener implements ShouldQueue
{
    use Queueable;

    /**
     * Handle the PrescriptionFailed event.
     *
     * @param PrescriptionFailed $event
     * @return void
     */
    public function handle(PrescriptionFailed $event): void
    {
        dispatch(new CancelOrderJob(
            $event->aggregateUuid,
            ['reason' => 'Prescription creation failed', ...$event->payload]
        ))->onQueue('order-fulfillment');
    }
}

