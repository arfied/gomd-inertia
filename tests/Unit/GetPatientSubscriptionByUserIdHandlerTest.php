<?php

use App\Application\Patient\PatientSubscriptionFinder;
use App\Application\Patient\Queries\GetPatientSubscriptionByUserId;
use App\Application\Patient\Queries\GetPatientSubscriptionByUserIdHandler;
use App\Models\Subscription;

it('returns a subscription for a user when one exists', function () {
    $expected = new Subscription();
    $expected->id = 123;
    $expected->user_id = 42;

    $finder = new class($expected) implements PatientSubscriptionFinder {
        public function __construct(private ?Subscription $result)
        {
        }

        public function findCurrentByUserId(int $userId): ?Subscription
        {
            return $this->result;
        }
    };

    $handler = new GetPatientSubscriptionByUserIdHandler($finder);

    $query = new GetPatientSubscriptionByUserId(userId: 42);

    $result = $handler->handle($query);

    expect($result)->toBe($expected);
});

it('returns null when no subscription exists for the given user', function () {
    $finder = new class(null) implements PatientSubscriptionFinder {
        public function __construct(private ?Subscription $result)
        {
        }

        public function findCurrentByUserId(int $userId): ?Subscription
        {
            return $this->result;
        }
    };

    $handler = new GetPatientSubscriptionByUserIdHandler($finder);

    $query = new GetPatientSubscriptionByUserId(userId: 999);

    $result = $handler->handle($query);

    expect($result)->toBeNull();
});

