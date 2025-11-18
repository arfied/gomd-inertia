<?php

namespace App\Jobs\Order;

use App\Domain\Order\Events\PrescriptionCreated;
use App\Domain\Order\Events\PrescriptionFailed;
use App\Services\EventStore;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * CreatePrescriptionJob - Step 2 of order fulfillment saga.
 *
 * Creates a prescription for the order.
 * On failure, publishes PrescriptionFailed event for compensation.
 */
class CreatePrescriptionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        private string $orderId,
        private array $orderData,
    ) {
    }

    public function handle(EventStore $eventStore): void
    {
        try {
            // Validate order data
            if (empty($this->orderData['patient_id'])) {
                throw new \InvalidArgumentException('Patient ID is required');
            }

            // Create prescription (external service call or domain logic)
            $prescriptionId = $this->createPrescription(
                $this->orderData['patient_id'],
                $this->orderData['medications'] ?? []
            );

            // Publish success event
            $event = new PrescriptionCreated(
                $this->orderId,
                [
                    'prescription_id' => $prescriptionId,
                    'patient_id' => $this->orderData['patient_id'],
                    'medications' => $this->orderData['medications'] ?? [],
                ],
                ['job_id' => $this->job?->getJobId()]
            );

            $eventStore->store($event);
            event($event);

        } catch (Throwable $e) {
            // Publish failure event for compensation
            $event = new PrescriptionFailed(
                $this->orderId,
                [
                    'error' => $e->getMessage(),
                    'order_data' => $this->orderData,
                ],
                ['exception' => get_class($e)]
            );

            $eventStore->store($event);
            event($event);

            throw $e;
        }
    }

    /**
     * Create prescription via external service or domain logic.
     */
    private function createPrescription(string $patientId, array $medications): string
    {
        // TODO: Implement prescription creation logic
        // This could call an external pharmacy service, create a database record, etc.
        // For now, return a mock prescription ID
        return 'RX-' . uniqid();
    }

    public function failed(Throwable $exception): void
    {
        // Log failure for monitoring
        \Log::error('CreatePrescriptionJob failed', [
            'order_id' => $this->orderId,
            'exception' => $exception->getMessage(),
        ]);
    }
}

