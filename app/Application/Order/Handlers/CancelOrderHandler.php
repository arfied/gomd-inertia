<?php

namespace App\Application\Order\Handlers;

use App\Application\Commands\CommandHandler;
use App\Application\Order\Commands\CancelOrder;
use App\Domain\Order\OrderAggregate;
use App\Domain\Shared\Commands\Command;
use App\Services\EventStoreContract;
use Illuminate\Contracts\Events\Dispatcher;
use InvalidArgumentException;

class CancelOrderHandler implements CommandHandler
{
    public function __construct(
        private EventStoreContract $eventStore,
        private Dispatcher $events,
    ) {
    }

    public function handle(Command $command): void
    {
        if (! $command instanceof CancelOrder) {
            throw new InvalidArgumentException('CancelOrderHandler can only handle CancelOrder commands');
        }

        $payload = [
            'status' => 'rejected',
            'rejection_reason' => $command->reason,
        ];

        $order = OrderAggregate::cancel(
            $command->orderUuid,
            $payload,
            $command->metadata,
        );

        foreach ($order->releaseEvents() as $event) {
            $this->eventStore->store($event);
            $this->events->dispatch($event);
        }
    }
}

