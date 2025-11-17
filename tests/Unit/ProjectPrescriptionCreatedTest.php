<?php

use App\Application\Prescription\PrescriptionProjector;
use App\Domain\Prescription\Events\PrescriptionCreated;
use App\Listeners\ProjectPrescriptionCreated;

it('projects prescriptions when the event is handled', function () {
    $fakeProjector = new class implements PrescriptionProjector {
        /** @var array<int, PrescriptionCreated> */
        public array $captured = [];

        public function projectPrescriptionCreated(PrescriptionCreated $event): void
        {
            $this->captured[] = $event;
        }
    };

    $listener = new ProjectPrescriptionCreated($fakeProjector);

    $event = new PrescriptionCreated(
        'rx-uuid-abc',
        ['user_id' => 123],
        ['source' => 'test'],
    );

    $listener->handle($event);

    expect($fakeProjector->captured)->toHaveCount(1)
        ->and($fakeProjector->captured[0])->toBe($event);
});

