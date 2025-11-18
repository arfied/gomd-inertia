<?php

namespace App\Application\Order\Handlers;

use App\Application\Commands\CommandHandler;
use App\Application\Order\Commands\StartOrderFulfillmentSaga;
use App\Domain\Order\OrderFulfillmentSaga;
use App\Domain\Shared\Commands\Command;
use App\Services\EventStoreContract;
use Illuminate\Contracts\Events\Dispatcher;
use InvalidArgumentException;

/**
 * StartOrderFulfillmentSagaHandler
 *
 * Handles the StartOrderFulfillmentSaga command.
 * Initiates the order fulfillment saga when an order is created.
 *
 * This is the entry point for the saga orchestration.
 */
class StartOrderFulfillmentSagaHandler implements CommandHandler
{
    public function __construct(
        private EventStoreContract $eventStore,
        private Dispatcher $events,
    ) {
    }

    public function handle(Command $command): void
    {
        if (! $command instanceof StartOrderFulfillmentSaga) {
            throw new InvalidArgumentException('StartOrderFulfillmentSagaHandler can only handle StartOrderFulfillmentSaga commands');
        }

        // Create and start the saga aggregate
        $saga = OrderFulfillmentSaga::start(
            $command->sagaUuid,
            $command->orderUuid,
            $command->metadata
        );

        // Store and dispatch all recorded events
        foreach ($saga->releaseEvents() as $event) {
            $this->eventStore->store($event);
            $this->events->dispatch($event);
        }
    }
}

