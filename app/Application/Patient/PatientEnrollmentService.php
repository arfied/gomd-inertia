<?php

namespace App\Application\Patient;

use App\Application\Commands\CommandBus;
use App\Application\Patient\Commands\EnrollPatient;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Application service that bridges the existing User model
 * to the event-sourced patient enrollment flow.
 */
class PatientEnrollmentService
{
    public function __construct(
        private CommandBus $commandBus,
    ) {
    }

    /**
     * Enroll the given user as a patient by dispatching an EnrollPatient command.
     */
    public function enroll(User $user, array $metadata = []): void
    {
        $command = new EnrollPatient(
            patientUuid: (string) Str::uuid(),
            userId: $user->id,
            metadata: $metadata,
        );

        $this->commandBus->dispatch($command);
    }
}

