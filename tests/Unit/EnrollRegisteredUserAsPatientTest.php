<?php

use App\Application\Commands\CommandBus;
use App\Application\Patient\PatientEnrollmentService;
use App\Domain\Shared\Commands\Command;
use App\Listeners\EnrollRegisteredUserAsPatient;
use App\Models\User;
use Illuminate\Auth\Events\Registered;

it('enrolls a registered user as a patient via the listener', function () {
    $fakeBus = new class extends CommandBus {
        /** @var array<int, Command> */
        public array $dispatched = [];

        public function dispatch(Command $command): void
        {
            $this->dispatched[] = $command;
        }
    };

    $fakeService = new class($fakeBus) extends PatientEnrollmentService {
        /** @var array<int, array{user: User, metadata: array}> */
        public array $enrolled = [];

        public function enroll(User $user, array $metadata = []): void
        {
            $this->enrolled[] = [
                'user' => $user,
                'metadata' => $metadata,
            ];
        }
    };

    $listener = new EnrollRegisteredUserAsPatient($fakeService);

    $user = new User();
    $user->id = 456;

    $event = new Registered($user);

    $listener->handle($event);

    expect($fakeService->enrolled)->toHaveCount(1)
        ->and($fakeService->enrolled[0]['user'])->toBe($user)
        ->and($fakeService->enrolled[0]['metadata'])->toMatchArray([
            'source' => 'registration',
        ]);
});

