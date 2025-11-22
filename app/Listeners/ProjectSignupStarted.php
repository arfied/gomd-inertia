<?php

namespace App\Listeners;

use App\Domain\Signup\Events\SignupStarted;
use App\Models\SignupReadModel;

class ProjectSignupStarted
{
    public function handle(SignupStarted $event): void
    {
        SignupReadModel::updateOrCreate(
            ['signup_uuid' => $event->aggregateUuid],
            [
                'user_id' => $event->userId,
                'signup_path' => $event->signupPath,
                'status' => 'pending',
                'created_at' => $event->occurredAt,
                'updated_at' => $event->occurredAt,
            ],
        );
    }
}

