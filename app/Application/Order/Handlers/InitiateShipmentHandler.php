<?php

namespace App\Application\Order\Handlers;

use App\Application\Commands\CommandHandler;
use App\Application\Order\Commands\InitiateShipment;
use App\Domain\Order\Events\ShipmentInitiated;
use App\Domain\Order\Events\ShipmentInitiationFailed;
use App\Domain\Shared\Commands\Command;
use App\Services\EventStoreContract;
use Illuminate\Contracts\Events\Dispatcher;
use InvalidArgumentException;

/**
 * InitiateShipmentHandler
 *
 * Handles the InitiateShipment command.
 * Part of the order fulfillment saga (Step 3).
 *
 * Initiates shipment of the order to the patient.
 * Emits either ShipmentInitiated or ShipmentInitiationFailed event.
 */
class InitiateShipmentHandler implements CommandHandler
{
    public function __construct(
        private EventStoreContract $eventStore,
        private Dispatcher $events,
    ) {
    }

    public function handle(Command $command): void
    {
        if (! $command instanceof InitiateShipment) {
            throw new InvalidArgumentException('InitiateShipmentHandler can only handle InitiateShipment commands');
        }

        // TODO: Implement shipment initiation logic
        // This should call your shipping service/API to initiate shipment
        // Example:
        // $shippingService = app(ShippingService::class);
        // $shipmentResult = $shippingService->initiate(
        //     orderUuid: $command->orderUuid,
        //     shippingAddress: $command->shippingAddress,
        //     shippingMethod: $command->shippingMethod,
        // );

        // For now, we'll emit a success event
        // In production, wrap this in try-catch and emit ShipmentInitiationFailed on error
        $payload = [
            'order_uuid' => $command->orderUuid,
            'saga_uuid' => $command->sagaUuid,
            'shipping_address' => $command->shippingAddress,
            'shipping_method' => $command->shippingMethod,
            'tracking_number' => $command->trackingNumber,
            'initiated_at' => now()->toIso8601String(),
            'status' => 'initiated',
        ];

        $event = new ShipmentInitiated(
            $command->orderUuid,
            $payload,
            array_merge($command->metadata, ['saga_uuid' => $command->sagaUuid])
        );

        $this->eventStore->store($event);
        $this->events->dispatch($event);
    }
}

