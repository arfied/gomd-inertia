<?php

namespace App\Listeners;

use App\Domain\Signup\Events\SubscriptionCreated;
use App\Models\SignupReadModel;

class ProjectSubscriptionCreated
{
    public function handle(SubscriptionCreated $event): void
    {
        $signup = SignupReadModel::where('signup_uuid', $event->aggregateUuid)->first();

        if ($signup) {
            $signup->update([
                'subscription_id' => $event->subscriptionId,
                'status' => 'completed',
                'updated_at' => $event->occurredAt,
            ]);
        }
    }
}

