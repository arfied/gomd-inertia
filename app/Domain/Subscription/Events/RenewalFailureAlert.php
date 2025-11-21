<?php

namespace App\Domain\Subscription\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when a subscription renewal fails after all retry attempts
 */
class RenewalFailureAlert
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $sagaUuid,
        public int $subscriptionId,
        public int $userId,
        public float $amount,
        public string $reason,
        public int $attemptNumber,
        public int $maxAttempts,
        public string $correlationId,
    ) {
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}

