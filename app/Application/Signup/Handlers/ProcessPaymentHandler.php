<?php

namespace App\Application\Signup\Handlers;

use App\Application\Commands\CommandHandler;
use App\Application\Signup\Commands\ProcessPayment;
use App\Domain\Shared\Commands\Command;
use App\Domain\Signup\SignupAggregate;
use App\Services\EventStoreContract;
use Illuminate\Contracts\Events\Dispatcher;
use InvalidArgumentException;

class ProcessPaymentHandler implements CommandHandler
{
    public function __construct(
        private EventStoreContract $eventStore,
        private Dispatcher $events,
    ) {
    }

    public function handle(Command $command): void
    {
        if (! $command instanceof ProcessPayment) {
            throw new InvalidArgumentException('ProcessPaymentHandler can only handle ProcessPayment commands');
        }

        $signup = SignupAggregate::fromEventStream($command->signupId);

        $signup->processPayment($command->paymentId, $command->amount, $command->status);

        foreach ($signup->releaseEvents() as $event) {
            $this->eventStore->store($event);
            $this->events->dispatch($event);
        }
    }
}

