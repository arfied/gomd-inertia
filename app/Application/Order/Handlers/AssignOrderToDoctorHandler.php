<?php

namespace App\Application\Order\Handlers;

use App\Application\Commands\CommandHandler;
use App\Application\Order\Commands\AssignOrderToDoctor;
use App\Domain\Order\OrderAggregate;
use App\Domain\Shared\Commands\Command;
use App\Services\EventStoreContract;
use Illuminate\Contracts\Events\Dispatcher;
use InvalidArgumentException;

class AssignOrderToDoctorHandler implements CommandHandler
{
    public function __construct(
        private EventStoreContract $eventStore,
        private Dispatcher $events,
    ) {
    }

    public function handle(Command $command): void
    {
        if (! $command instanceof AssignOrderToDoctor) {
            throw new InvalidArgumentException('AssignOrderToDoctorHandler can only handle AssignOrderToDoctor commands');
        }

        $payload = [
            'doctor_id' => $command->doctorId,
            'assigned_by' => $command->assignedByUserId,
            'status' => 'assigned',
        ];

        $order = OrderAggregate::assignDoctor(
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

