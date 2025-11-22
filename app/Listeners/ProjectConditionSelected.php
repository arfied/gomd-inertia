<?php

namespace App\Listeners;

use App\Domain\Signup\Events\ConditionSelected;
use App\Models\SignupReadModel;

class ProjectConditionSelected
{
    public function handle(ConditionSelected $event): void
    {
        $signup = SignupReadModel::where('signup_uuid', $event->aggregateUuid)->first();

        if ($signup) {
            $signup->update([
                'condition_id' => $event->conditionId,
                'updated_at' => $event->occurredAt,
            ]);
        }
    }
}

