<?php

use App\Application\Patient\PatientActivityProjector;
use App\Domain\Patient\Events\PatientEnrolled;
use App\Listeners\ProjectPatientActivity;

it('projects patient activity when the event is handled', function () {
    $fakeProjector = new class implements PatientActivityProjector {
        /** @var array<int, PatientEnrolled> */
        public array $captured = [];

        public function projectPatientEnrolled(PatientEnrolled $event): void
        {
            $this->captured[] = $event;
        }
    };

    $listener = new ProjectPatientActivity($fakeProjector);

    $event = new PatientEnrolled(
        'patient-uuid-abc',
        ['user_id' => 123],
        ['source' => 'registration'],
    );

    $listener->handle($event);

    expect($fakeProjector->captured)->toHaveCount(1)
        ->and($fakeProjector->captured[0])->toBe($event);
});

