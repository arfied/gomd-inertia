<?php

namespace App\Jobs\Order;

use App\Domain\Order\Events\OrderCancelled;
use App\Services\EventStore;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * CancelOrderJob - Compensation action for order cancellation.
 *
 * Triggered when prescription creation fails.
 * Cancels the order and publishes OrderCancelled event.
 */
class CancelOrderJob implements ShouldQueue
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
            // Cancel order (external service call or domain logic)
            $this->cancelOrder($this->orderId);

            // Publish cancellation event
            $event = new OrderCancelled(
                $this->orderId,
                [
                    'reason' => $this->compensationData['reason'] ?? 'Saga compensation',
                    'cancelled_at' => now()->toIso8601String(),
                ],
                ['compensation_data' => $this->compensationData]
            );

            $eventStore->store($event);
            event($event);

            \Log::info('Order cancelled via compensation', [
                'order_id' => $this->orderId,
                'reason' => $this->compensationData['reason'] ?? 'Unknown',
            ]);

        } catch (Throwable $e) {
            \Log::error('CancelOrderJob failed', [
                'order_id' => $this->orderId,
                'exception' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Cancel order via external service or domain logic.
     */
    private function cancelOrder(string $orderId): void
    {
        // TODO: Implement order cancellation logic
        // This could update order status, notify customer, etc.
    }

    public function failed(Throwable $exception): void
    {
        \Log::error('CancelOrderJob failed permanently', [
            'order_id' => $this->orderId,
            'exception' => $exception->getMessage(),
        ]);
    }
}

