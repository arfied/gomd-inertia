<?php

use App\Application\Patient\PatientEnrollmentProjector;
use App\Domain\Patient\Events\PatientEnrolled;
use App\Listeners\ProjectPatientEnrollment;

it('projects patient enrollment when the event is handled', function () {
    $fakeProjector = new class implements PatientEnrollmentProjector {
        /** @var array<int, PatientEnrolled> */
        public array $captured = [];

        public function project(PatientEnrolled $event): void
        {
            $this->captured[] = $event;
        }
    };

    $listener = new ProjectPatientEnrollment($fakeProjector);

    $event = new PatientEnrolled(
        'patient-uuid-abc',
        ['user_id' => 123],
        ['source' => 'registration'],
    );

    $listener->handle($event);

    expect($fakeProjector->captured)->toHaveCount(1)
        ->and($fakeProjector->captured[0])->toBe($event);
});

