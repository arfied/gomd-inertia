<?php

namespace App\Jobs\Order;

use App\Domain\Order\Events\InventoryReserved;
use App\Domain\Order\Events\InventoryReservationFailed;
use App\Services\EventStore;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * ReserveInventoryJob - Step 3 of order fulfillment saga.
 *
 * Reserves inventory for the order.
 * On failure, publishes InventoryReservationFailed event for compensation.
 */
class ReserveInventoryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        private string $orderId,
        private array $prescriptionData,
    ) {
    }

    public function handle(EventStore $eventStore): void
    {
        try {
            // Validate prescription data
            if (empty($this->prescriptionData['prescription_id'])) {
                throw new \InvalidArgumentException('Prescription ID is required');
            }

            // Reserve inventory (external service call or domain logic)
            $reservationId = $this->reserveInventory(
                $this->prescriptionData['prescription_id'],
                $this->prescriptionData['medications'] ?? []
            );

            // Publish success event
            $event = new InventoryReserved(
                $this->orderId,
                [
                    'reservation_id' => $reservationId,
                    'prescription_id' => $this->prescriptionData['prescription_id'],
                    'medications' => $this->prescriptionData['medications'] ?? [],
                ],
                ['job_id' => $this->job?->getJobId()]
            );

            $eventStore->store($event);
            event($event);

        } catch (Throwable $e) {
            // Publish failure event for compensation
            $event = new InventoryReservationFailed(
                $this->orderId,
                [
                    'error' => $e->getMessage(),
                    'prescription_data' => $this->prescriptionData,
                ],
                ['exception' => get_class($e)]
            );

            $eventStore->store($event);
            event($event);

            throw $e;
        }
    }

    /**
     * Reserve inventory via external service or domain logic.
     */
    private function reserveInventory(string $prescriptionId, array $medications): string
    {
        // TODO: Implement inventory reservation logic
        // This could call an inventory management service, update database records, etc.
        // For now, return a mock reservation ID
        return 'INV-' . uniqid();
    }

    public function failed(Throwable $exception): void
    {
        // Log failure for monitoring
        \Log::error('ReserveInventoryJob failed', [
            'order_id' => $this->orderId,
            'exception' => $exception->getMessage(),
        ]);
    }
}

