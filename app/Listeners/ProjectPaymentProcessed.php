<?php

namespace App\Listeners;

use App\Domain\Signup\Events\PaymentProcessed;
use App\Models\SignupReadModel;

class ProjectPaymentProcessed
{
    public function handle(PaymentProcessed $event): void
    {
        $signup = SignupReadModel::where('signup_uuid', $event->aggregateUuid)->first();

        if ($signup) {
            $signup->update([
                'payment_id' => $event->paymentId,
                'payment_amount' => $event->amount,
                'payment_status' => $event->status,
                'updated_at' => $event->occurredAt,
            ]);
        }
    }
}

