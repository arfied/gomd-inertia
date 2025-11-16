<?php

use App\Application\Patient\PatientActivityFinder;
use App\Application\Patient\Queries\GetRecentPatientActivityByUserId;
use App\Application\Patient\Queries\GetRecentPatientActivityByUserIdHandler;
use App\Models\Activity;
use Illuminate\Support\Collection;

it('returns recent activities for a user when they exist', function () {
    $activity = new Activity();
    $activity->id = 1;
    $activity->type = 'patient.enrolled';

    $expected = collect([$activity]);

    $finder = new class($expected) implements PatientActivityFinder {
        public function __construct(private Collection $result)
        {
        }

        public function findRecentByUserId(int $userId, int $limit = 5): Collection
        {
            return $this->result;
        }
    };

    $handler = new GetRecentPatientActivityByUserIdHandler($finder);

    $query = new GetRecentPatientActivityByUserId(userId: 42, limit: 10);

    $result = $handler->handle($query);

    expect($result)->toBe($expected);
});

it('returns an empty collection when no activities exist for the given user', function () {
    $expected = collect();

    $finder = new class($expected) implements PatientActivityFinder {
        public function __construct(private Collection $result)
        {
        }

        public function findRecentByUserId(int $userId, int $limit = 5): Collection
        {
            return $this->result;
        }
    };

    $handler = new GetRecentPatientActivityByUserIdHandler($finder);

    $query = new GetRecentPatientActivityByUserId(userId: 999, limit: 5);

    $result = $handler->handle($query);

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result)->toHaveCount(0);
});

