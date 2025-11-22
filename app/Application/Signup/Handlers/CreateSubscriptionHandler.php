<?php

namespace App\Application\Signup\Handlers;

use App\Application\Commands\CommandHandler;
use App\Application\Signup\Commands\CreateSubscription;
use App\Domain\Shared\Commands\Command;
use App\Domain\Signup\SignupAggregate;
use App\Services\EventStoreContract;
use Illuminate\Contracts\Events\Dispatcher;
use InvalidArgumentException;

class CreateSubscriptionHandler implements CommandHandler
{
    public function __construct(
        private EventStoreContract $eventStore,
        private Dispatcher $events,
    ) {
    }

    public function handle(Command $command): void
    {
        if (! $command instanceof CreateSubscription) {
            throw new InvalidArgumentException('CreateSubscriptionHandler can only handle CreateSubscription commands');
        }

        $signup = SignupAggregate::fromEventStream($command->signupId);

        $signup->createSubscription(
            $command->subscriptionId,
            $command->userId,
            $command->planId,
            $command->medicationId,
            $command->conditionId,
        );

        foreach ($signup->releaseEvents() as $event) {
            $this->eventStore->store($event);
            $this->events->dispatch($event);
        }
    }
}

