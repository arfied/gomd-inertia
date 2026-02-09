<?php

namespace App\Application\Signup\Handlers;

use App\Application\Commands\CommandHandler;
use App\Application\Signup\Commands\CreatePatientUser;
use App\Domain\Shared\Commands\Command;
use App\Domain\Signup\SignupAggregate;
use App\Services\EventStoreContract;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CreatePatientUserHandler implements CommandHandler
{
    public function __construct(
        private EventStoreContract $eventStore,
        private Dispatcher $events,
    ) {
    }

    public function handle(Command $command): void
    {
        if (!$command instanceof CreatePatientUser) {
            throw new InvalidArgumentException('CreatePatientUserHandler can only handle CreatePatientUser commands');
        }

        $signup = SignupAggregate::fromEventStream($command->signupId);

        // Generate a random password for the user
        $randomPassword = Str::random(16);

        // Create patient user event
        $signup->createPatientUser(
            $command->email,
            $randomPassword,
            $command->metadata,
        );

        foreach ($signup->releaseEvents() as $event) {
            $this->eventStore->store($event);
            $this->events->dispatch($event);
        }
    }
}

