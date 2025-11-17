<?php

namespace App\Notifications;

use App\Models\MedicationOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Notification stub for when a medication order status changes.
 */
class MedicationOrderStatusChanged extends Notification
{
    use Queueable;

    public function __construct(
        public MedicationOrder $order,
        public string $previousStatus,
    ) {
    }

    public function via(object $notifiable): array
    {
        // No-op for now; can be expanded to mail/database/etc later.
        return [];
    }
}

