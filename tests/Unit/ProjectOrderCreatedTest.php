<?php

use App\Application\Order\OrderProjector;
use App\Domain\Order\Events\OrderCreated;
use App\Listeners\ProjectOrderCreated;

it('projects orders when the event is handled', function () {
    $fakeProjector = new class implements OrderProjector {
        /** @var array<int, OrderCreated> */
        public array $captured = [];

        public function projectOrderCreated(OrderCreated $event): void
        {
            $this->captured[] = $event;
        }
    };

    $listener = new ProjectOrderCreated($fakeProjector);

    $event = new OrderCreated(
        'order-uuid-abc',
        ['patient_id' => 123],
        ['source' => 'test'],
    );

    $listener->handle($event);

    expect($fakeProjector->captured)->toHaveCount(1)
        ->and($fakeProjector->captured[0])->toBe($event);
});

