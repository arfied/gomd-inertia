<?php

namespace App\Application\Signup\Handlers;

use App\Application\Commands\CommandHandler;
use App\Application\Signup\Commands\StartSignup;
use App\Domain\Shared\Commands\Command;
use App\Domain\Signup\SignupAggregate;
use App\Services\EventStoreContract;
use Illuminate\Contracts\Events\Dispatcher;
use InvalidArgumentException;

class StartSignupHandler implements CommandHandler
{
    public function __construct(
        private EventStoreContract $eventStore,
        private Dispatcher $events,
    ) {
    }

    public function handle(Command $command): void
    {
        if (! $command instanceof StartSignup) {
            throw new InvalidArgumentException('StartSignupHandler can only handle StartSignup commands');
        }

        $payload = [
            'user_id' => $command->userId,
            'signup_path' => $command->signupPath,
        ];

        $signup = SignupAggregate::startSignup(
            $command->signupId,
            $payload,
        );

        foreach ($signup->releaseEvents() as $event) {
            $this->eventStore->store($event);
            $this->events->dispatch($event);
        }
    }
}

