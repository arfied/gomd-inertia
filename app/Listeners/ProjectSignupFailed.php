<?php

namespace App\Listeners;

use App\Domain\Signup\Events\SignupFailed;
use App\Models\SignupReadModel;

class ProjectSignupFailed
{
    public function handle(SignupFailed $event): void
    {
        $signup = SignupReadModel::where('signup_uuid', $event->aggregateUuid)->first();

        if ($signup) {
            $signup->update([
                'status' => 'failed',
                'failure_reason' => $event->reason,
                'failure_message' => $event->message,
                'updated_at' => $event->occurredAt,
            ]);
        }
    }
}

