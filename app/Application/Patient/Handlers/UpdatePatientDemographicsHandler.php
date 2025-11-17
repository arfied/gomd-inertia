<?php

namespace App\Application\Patient\Handlers;

use App\Application\Commands\CommandHandler;
use App\Application\Patient\Commands\UpdatePatientDemographics;
use App\Domain\Patient\Events\PatientDemographicsUpdated;
use App\Domain\Shared\Commands\Command;
use App\Services\EventStoreContract;
use Illuminate\Contracts\Events\Dispatcher;
use InvalidArgumentException;

/**
 * Command handler that persists a PatientDemographicsUpdated event.
 */
class UpdatePatientDemographicsHandler implements CommandHandler
{
    public function __construct(
        private EventStoreContract $eventStore,
        private Dispatcher $events,
    ) {
    }

    public function handle(Command $command): void
    {
        if (! $command instanceof UpdatePatientDemographics) {
            throw new InvalidArgumentException('UpdatePatientDemographicsHandler can only handle UpdatePatientDemographics commands.');
        }

        $payload = $this->buildPayload($command);

        $event = new PatientDemographicsUpdated(
            $command->patientUuid,
            $payload,
            $command->metadata,
        );

        $this->eventStore->store($event);

        $this->events->dispatch($event);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPayload(UpdatePatientDemographics $command): array
    {
        $allowedFields = [
            'fname',
            'lname',
            'gender',
            'dob',
            'address1',
            'address2',
            'city',
            'state',
            'zip',
            'phone',
            'mobile_phone',
        ];

        $payload = [
            'user_id' => $command->userId,
        ];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $command->demographics)) {
                $payload[$field] = $command->demographics[$field];
            }
        }

        return $payload;
    }
}

