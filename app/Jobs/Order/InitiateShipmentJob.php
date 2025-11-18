<?php

namespace App\Jobs\Order;

use App\Domain\Order\Events\ShipmentInitiated;
use App\Domain\Order\Events\ShipmentInitiationFailed;
use App\Services\EventStore;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * InitiateShipmentJob - Step 4 of order fulfillment saga.
 *
 * Initiates shipment for the order.
 * On failure, publishes ShipmentInitiationFailed event for compensation.
 */
class InitiateShipmentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        private string $orderId,
        private array $inventoryData,
    ) {
    }

    public function handle(EventStore $eventStore): void
    {
        try {
            // Validate inventory data
            if (empty($this->inventoryData['reservation_id'])) {
                throw new \InvalidArgumentException('Reservation ID is required');
            }

            // Initiate shipment (external service call or domain logic)
            $shipmentId = $this->initiateShipment(
                $this->inventoryData['reservation_id'],
                $this->inventoryData['medications'] ?? []
            );

            // Publish success event
            $event = new ShipmentInitiated(
                $this->orderId,
                [
                    'shipment_id' => $shipmentId,
                    'reservation_id' => $this->inventoryData['reservation_id'],
                    'medications' => $this->inventoryData['medications'] ?? [],
                    'initiated_at' => now()->toIso8601String(),
                ],
                ['job_id' => $this->job?->getJobId()]
            );

            $eventStore->store($event);
            event($event);

        } catch (Throwable $e) {
            // Publish failure event for compensation
            $event = new ShipmentInitiationFailed(
                $this->orderId,
                [
                    'error' => $e->getMessage(),
                    'inventory_data' => $this->inventoryData,
                ],
                ['exception' => get_class($e)]
            );

            $eventStore->store($event);
            event($event);

            throw $e;
        }
    }

    /**
     * Initiate shipment via external service or domain logic.
     */
    private function initiateShipment(string $reservationId, array $medications): string
    {
        // TODO: Implement shipment initiation logic
        // This could call a shipping provider API, create shipment records, etc.
        // For now, return a mock shipment ID
        return 'SHIP-' . uniqid();
    }

    public function failed(Throwable $exception): void
    {
        // Log failure for monitoring
        \Log::error('InitiateShipmentJob failed', [
            'order_id' => $this->orderId,
            'exception' => $exception->getMessage(),
        ]);
    }
}

