<?php

namespace App\Application\Order\Handlers;

use App\Domain\Order\Events\OrderCreated;
use App\Domain\Order\Events\PrescriptionCreated;
use App\Domain\Order\Events\PrescriptionFailed;
use App\Domain\Order\Events\InventoryReserved;
use App\Domain\Order\Events\InventoryReservationFailed;
use App\Domain\Order\Events\ShipmentInitiated;
use App\Domain\Order\Events\ShipmentInitiationFailed;
use App\Jobs\Order\CreatePrescriptionJob;
use App\Jobs\Order\ReserveInventoryJob;
use App\Jobs\Order\InitiateShipmentJob;
use App\Jobs\Order\CancelOrderJob;
use App\Jobs\Order\CancelPrescriptionJob;
use App\Jobs\Order\ReleaseInventoryJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * OrderFulfillmentSagaHandler - Orchestrates saga state transitions.
 *
 * Listens to domain events and dispatches next steps or compensation actions.
 *
 * Laravel 12 automatically discovers this handler via event type-hints.
 * Each method handles a specific event type.
 */
class OrderFulfillmentSagaHandler implements ShouldQueue
{
    use Queueable;

    /**
     * Handle OrderCreated event - dispatch prescription creation.
     */
    public function handle(OrderCreated $event): void
    {
        dispatch(new CreatePrescriptionJob(
            $event->aggregateUuid,
            $event->payload
        ))->onQueue('order-fulfillment');
    }
}

/**
 * PrescriptionCreatedHandler - Dispatch inventory reservation.
 */
class PrescriptionCreatedHandler implements ShouldQueue
{
    use Queueable;

    public function handle(PrescriptionCreated $event): void
    {
        dispatch(new ReserveInventoryJob(
            $event->aggregateUuid,
            $event->payload
        ))->onQueue('order-fulfillment');
    }
}

/**
 * PrescriptionFailedHandler - Trigger compensation.
 */
class PrescriptionFailedHandler implements ShouldQueue
{
    use Queueable;

    public function handle(PrescriptionFailed $event): void
    {
        dispatch(new CancelOrderJob(
            $event->aggregateUuid,
            ['reason' => 'Prescription creation failed', ...$event->payload]
        ))->onQueue('order-fulfillment');
    }
}

/**
 * InventoryReservedHandler - Dispatch shipment initiation.
 */
class InventoryReservedHandler implements ShouldQueue
{
    use Queueable;

    public function handle(InventoryReserved $event): void
    {
        dispatch(new InitiateShipmentJob(
            $event->aggregateUuid,
            $event->payload
        ))->onQueue('order-fulfillment');
    }
}

/**
 * InventoryReservationFailedHandler - Trigger compensation.
 */
class InventoryReservationFailedHandler implements ShouldQueue
{
    use Queueable;

    public function handle(InventoryReservationFailed $event): void
    {
        dispatch(new CancelPrescriptionJob(
            $event->aggregateUuid,
            ['reason' => 'Inventory reservation failed', ...$event->payload]
        ))->onQueue('order-fulfillment');
    }
}

/**
 * ShipmentInitiatedHandler - Mark saga complete.
 */
class ShipmentInitiatedHandler implements ShouldQueue
{
    use Queueable;

    public function handle(ShipmentInitiated $event): void
    {
        // Saga completes successfully
        // Dispatch any post-fulfillment actions (notifications, analytics, etc.)
    }
}

/**
 * ShipmentInitiationFailedHandler - Trigger full compensation.
 */
class ShipmentInitiationFailedHandler implements ShouldQueue
{
    use Queueable;

    public function handle(ShipmentInitiationFailed $event): void
    {
        dispatch(new ReleaseInventoryJob(
            $event->aggregateUuid,
            ['reason' => 'Shipment initiation failed', ...$event->payload]
        ))->onQueue('order-fulfillment');
    }
}

