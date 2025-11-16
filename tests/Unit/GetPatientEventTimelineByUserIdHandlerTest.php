<?php

use App\Application\Patient\PatientTimelineFinder;
use App\Application\Patient\Queries\GetPatientEventTimelineByUserId;
use App\Application\Patient\Queries\GetPatientEventTimelineByUserIdHandler;
use App\Models\StoredEvent;
use Illuminate\Support\Collection;

it('returns timeline events for a user when they exist', function () {
    $event = new StoredEvent();
    $event->id = 1;
    $event->event_type = 'patient.enrolled';

    $expected = collect([$event]);

    $finder = new class($expected) implements PatientTimelineFinder {
        public function __construct(private Collection $result)
        {
        }

        public function findTimelineByUserId(int $userId, int $limit = 50): Collection
        {
            return $this->result;
        }
    };

    $handler = new GetPatientEventTimelineByUserIdHandler($finder);

    $query = new GetPatientEventTimelineByUserId(userId: 42, limit: 10);

    $result = $handler->handle($query);

    expect($result)->toBe($expected);
});

it('returns an empty collection when no events exist for the given user', function () {
    $expected = collect();

    $finder = new class($expected) implements PatientTimelineFinder {
        public function __construct(private Collection $result)
        {
        }

        public function findTimelineByUserId(int $userId, int $limit = 50): Collection
        {
            return $this->result;
        }
    };

    $handler = new GetPatientEventTimelineByUserIdHandler($finder);

    $query = new GetPatientEventTimelineByUserId(userId: 999, limit: 25);

    $result = $handler->handle($query);

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result)->toHaveCount(0);
});

