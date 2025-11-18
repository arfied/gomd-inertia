<?php

namespace App\Jobs\Order;

use App\Domain\Order\Events\InventoryReleased;
use App\Services\EventStore;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * ReleaseInventoryJob - Compensation action for inventory release.
 *
 * Triggered when shipment initiation fails.
 * Releases reserved inventory and publishes InventoryReleased event.
 * Followed by CancelPrescriptionJob.
 */
class ReleaseInventoryJob implements ShouldQueue
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
            // Release inventory (external service call or domain logic)
            $this->releaseInventory($this->orderId);

            // Publish release event
            $event = new InventoryReleased(
                $this->orderId,
                [
                    'reason' => $this->compensationData['reason'] ?? 'Saga compensation',
                    'released_at' => now()->toIso8601String(),
                ],
                ['compensation_data' => $this->compensationData]
            );

            $eventStore->store($event);
            event($event);

            // Dispatch next compensation step
            dispatch(new CancelPrescriptionJob(
                $this->orderId,
                ['reason' => 'Inventory released - ' . ($this->compensationData['reason'] ?? 'Unknown')]
            ))->onQueue('order-fulfillment');

            \Log::info('Inventory released via compensation', [
                'order_id' => $this->orderId,
                'reason' => $this->compensationData['reason'] ?? 'Unknown',
            ]);

        } catch (Throwable $e) {
            \Log::error('ReleaseInventoryJob failed', [
                'order_id' => $this->orderId,
                'exception' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Release inventory via external service or domain logic.
     */
    private function releaseInventory(string $orderId): void
    {
        // TODO: Implement inventory release logic
        // This could call inventory management service, update reservation status, etc.
    }

    public function failed(Throwable $exception): void
    {
        \Log::error('ReleaseInventoryJob failed permanently', [
            'order_id' => $this->orderId,
            'exception' => $exception->getMessage(),
        ]);
    }
}

