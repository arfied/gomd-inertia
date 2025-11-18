<?php

namespace App\Jobs\Order;

use App\Domain\Order\Events\PrescriptionCancelled;
use App\Services\EventStore;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * CancelPrescriptionJob - Compensation action for prescription cancellation.
 *
 * Triggered when inventory reservation fails.
 * Cancels the prescription and publishes PrescriptionCancelled event.
 * Followed by CancelOrderJob.
 */
class CancelPrescriptionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        private string $orderId,
        private array $compensationData,
    ) {
    }

    public function handle(EventStore $eventStore): void
    {
        try {
            // Cancel prescription (external service call or domain logic)
            $this->cancelPrescription($this->orderId);

            // Publish cancellation event
            $event = new PrescriptionCancelled(
                $this->orderId,
                [
                    'reason' => $this->compensationData['reason'] ?? 'Saga compensation',
                    'cancelled_at' => now()->toIso8601String(),
                ],
                ['compensation_data' => $this->compensationData]
            );

            $eventStore->store($event);
            event($event);

            // Dispatch next compensation step
            dispatch(new CancelOrderJob(
                $this->orderId,
                ['reason' => 'Prescription cancelled - ' . ($this->compensationData['reason'] ?? 'Unknown')]
            ))->onQueue('order-fulfillment');

            \Log::info('Prescription cancelled via compensation', [
                'order_id' => $this->orderId,
                'reason' => $this->compensationData['reason'] ?? 'Unknown',
            ]);

        } catch (Throwable $e) {
            \Log::error('CancelPrescriptionJob failed', [
                'order_id' => $this->orderId,
                'exception' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Cancel prescription via external service or domain logic.
     */
    private function cancelPrescription(string $orderId): void
    {
        // TODO: Implement prescription cancellation logic
        // This could call pharmacy service, update prescription status, etc.
    }

    public function failed(Throwable $exception): void
    {
        \Log::error('CancelPrescriptionJob failed permanently', [
            'order_id' => $this->orderId,
            'exception' => $exception->getMessage(),
        ]);
    }
}

