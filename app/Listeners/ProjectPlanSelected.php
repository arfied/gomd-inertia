<?php

namespace App\Listeners;

use App\Domain\Signup\Events\PlanSelected;
use App\Models\SignupReadModel;

class ProjectPlanSelected
{
    public function handle(PlanSelected $event): void
    {
        $signup = SignupReadModel::where('signup_uuid', $event->aggregateUuid)->first();

        if ($signup) {
            $signup->update([
                'plan_id' => $event->planId,
                'updated_at' => $event->occurredAt,
            ]);
        }
    }
}

