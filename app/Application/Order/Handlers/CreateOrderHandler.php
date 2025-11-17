<?php

namespace App\Application\Order\Handlers;

use App\Application\Commands\CommandHandler;
use App\Application\Order\Commands\CreateOrder;
use App\Domain\Order\OrderAggregate;
use App\Domain\Shared\Commands\Command;
use App\Services\EventStoreContract;
use Illuminate\Contracts\Events\Dispatcher;
use InvalidArgumentException;

class CreateOrderHandler implements CommandHandler
{
    public function __construct(
        private EventStoreContract $eventStore,
        private Dispatcher $events,
    ) {
    }

    public function handle(Command $command): void
    {
        if (! $command instanceof CreateOrder) {
            throw new InvalidArgumentException('CreateOrderHandler can only handle CreateOrder commands');
        }

        $payload = [
            'patient_id' => $command->patientId,
            'doctor_id' => $command->doctorId,
            'prescription_id' => $command->prescriptionId,
            'status' => 'pending',
            'patient_notes' => $command->patientNotes,
            'doctor_notes' => $command->doctorNotes,
        ];

        $order = OrderAggregate::create(
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

