<?php

namespace App\Application\Prescription\Handlers;

use App\Application\Commands\CommandHandler;
use App\Application\Prescription\Commands\CreatePrescription;
use App\Domain\Prescription\PrescriptionAggregate;
use App\Domain\Shared\Commands\Command;
use App\Services\EventStoreContract;
use Illuminate\Contracts\Events\Dispatcher;
use InvalidArgumentException;

class CreatePrescriptionHandler implements CommandHandler
{
    public function __construct(
        private EventStoreContract $eventStore,
        private Dispatcher $events,
    ) {
    }

    public function handle(Command $command): void
    {
        if (! $command instanceof CreatePrescription) {
            throw new InvalidArgumentException('CreatePrescriptionHandler can only handle CreatePrescription commands');
        }

        $payload = [
            'user_id' => $command->patientId,
            'doctor_id' => $command->doctorId,
            'pharmacist_id' => null,
            'status' => 'pending',
            'notes' => $command->notes,
            'is_non_standard' => $command->isNonStandard,
        ];

        $prescription = PrescriptionAggregate::create(
            $command->prescriptionUuid,
            $payload,
            $command->metadata,
        );

        foreach ($prescription->releaseEvents() as $event) {
            $this->eventStore->store($event);
            $this->events->dispatch($event);
        }
    }
}

