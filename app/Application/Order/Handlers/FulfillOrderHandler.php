<?php

namespace App\Application\Order\Handlers;

use App\Application\Commands\CommandHandler;
use App\Application\Order\Commands\FulfillOrder;
use App\Domain\Order\OrderAggregate;
use App\Domain\Shared\Commands\Command;
use App\Services\EventStoreContract;
use Illuminate\Contracts\Events\Dispatcher;
use InvalidArgumentException;

class FulfillOrderHandler implements CommandHandler
{
    public function __construct(
        private EventStoreContract $eventStore,
        private Dispatcher $events,
    ) {
    }

    public function handle(Command $command): void
    {
        if (! $command instanceof FulfillOrder) {
            throw new InvalidArgumentException('FulfillOrderHandler can only handle FulfillOrder commands');
        }

        $payload = [
            'status' => 'completed',
        ];

        $order = OrderAggregate::fulfill(
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

