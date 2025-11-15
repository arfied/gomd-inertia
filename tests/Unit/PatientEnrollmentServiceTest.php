<?php

use App\Application\Commands\CommandBus;
use App\Application\Patient\Commands\EnrollPatient;
use App\Application\Patient\PatientEnrollmentService;
use App\Domain\Shared\Commands\Command;
use App\Models\User;

it('dispatches an EnrollPatient command for the given user', function () {
    $fakeBus = new class extends CommandBus {
        /** @var array<int, Command> */
        public array $dispatched = [];

        public function dispatch(Command $command): void
        {
            $this->dispatched[] = $command;
        }
    };

    $service = new PatientEnrollmentService($fakeBus);

    $user = new User();
    $user->id = 123;

    $metadata = ['source' => 'unit-test'];

    $service->enroll($user, $metadata);

    expect($fakeBus->dispatched)->toHaveCount(1);

    $command = $fakeBus->dispatched[0];

    expect($command)
        ->toBeInstanceOf(EnrollPatient::class)
        ->and($command->userId)->toBe(123)
        ->and($command->metadata)->toMatchArray($metadata)
        ->and($command->patientUuid)->not()->toBe('');
});

