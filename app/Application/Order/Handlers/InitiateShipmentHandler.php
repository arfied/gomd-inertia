<?php

namespace App\Application\Order\Handlers;

use App\Application\Commands\CommandHandler;
use App\Application\Order\Commands\InitiateShipment;
use App\Domain\Order\Events\ShipmentInitiated;
use App\Domain\Order\Events\ShipmentInitiationFailed;
use App\Domain\Shared\Commands\Command;
use App\Services\EventStoreContract;
use App\Services\ShipmentInitiationService;
use Illuminate\Contracts\Events\Dispatcher;
use InvalidArgumentException;
use Throwable;

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
        private ShipmentInitiationService $shipmentService,
    ) {
    }

    public function handle(Command $command): void
    {
        if (! $command instanceof InitiateShipment) {
            throw new InvalidArgumentException('InitiateShipmentHandler can only handle InitiateShipment commands');
        }

        try {
            // Call shipping service to initiate shipment
            $shipmentResult = $this->shipmentService->initiate(
                orderUuid: $command->orderUuid,
                shippingAddress: $command->shippingAddress,
                shippingMethod: $command->shippingMethod,
                trackingNumber: $command->trackingNumber,
            );

            // Emit appropriate event based on result
            if ($shipmentResult['success']) {
                $payload = [
                    'order_uuid' => $command->orderUuid,
                    'saga_uuid' => $command->sagaUuid,
                    'shipping_address' => $command->shippingAddress,
                    'shipping_method' => $command->shippingMethod,
                    'tracking_number' => $shipmentResult['trackingNumber'],
                    'shipment_id' => $shipmentResult['shipmentId'],
                    'initiated_at' => now()->toIso8601String(),
                    'status' => 'initiated',
                ];

                $event = new ShipmentInitiated(
                    $command->orderUuid,
                    $payload,
                    array_merge($command->metadata, ['saga_uuid' => $command->sagaUuid])
                );
            } else {
                $payload = [
                    'order_uuid' => $command->orderUuid,
                    'saga_uuid' => $command->sagaUuid,
                    'shipping_address' => $command->shippingAddress,
                    'shipping_method' => $command->shippingMethod,
                    'reason' => $shipmentResult['error'],
                    'failed_at' => now()->toIso8601String(),
                ];

                $event = new ShipmentInitiationFailed(
                    $command->orderUuid,
                    $payload,
                    array_merge($command->metadata, ['saga_uuid' => $command->sagaUuid])
                );
            }

            $this->eventStore->store($event);
            $this->events->dispatch($event);

        } catch (Throwable $e) {
            // Emit failure event on exception
            $payload = [
                'order_uuid' => $command->orderUuid,
                'saga_uuid' => $command->sagaUuid,
                'shipping_address' => $command->shippingAddress,
                'reason' => $e->getMessage(),
                'failed_at' => now()->toIso8601String(),
            ];

            $event = new ShipmentInitiationFailed(
                $command->orderUuid,
                $payload,
                array_merge($command->metadata, ['saga_uuid' => $command->sagaUuid, 'exception' => get_class($e)])
            );

            $this->eventStore->store($event);
            $this->events->dispatch($event);

            throw $e;
        }
    }
}

