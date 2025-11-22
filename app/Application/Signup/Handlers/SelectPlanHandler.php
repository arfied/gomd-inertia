<?php

namespace App\Application\Signup\Handlers;

use App\Application\Commands\CommandHandler;
use App\Application\Signup\Commands\SelectPlan;
use App\Domain\Shared\Commands\Command;
use App\Domain\Signup\SignupAggregate;
use App\Services\EventStoreContract;
use Illuminate\Contracts\Events\Dispatcher;
use InvalidArgumentException;

class SelectPlanHandler implements CommandHandler
{
    public function __construct(
        private EventStoreContract $eventStore,
        private Dispatcher $events,
    ) {
    }

    public function handle(Command $command): void
    {
        if (! $command instanceof SelectPlan) {
            throw new InvalidArgumentException('SelectPlanHandler can only handle SelectPlan commands');
        }

        $signup = SignupAggregate::fromEventStream($command->signupId);

        $signup->selectPlan($command->planId);

        foreach ($signup->releaseEvents() as $event) {
            $this->eventStore->store($event);
            $this->events->dispatch($event);
        }
    }
}

