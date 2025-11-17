<?php

namespace App\Notifications;

use App\Models\MedicationOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Notification stub for when a medication order is created.
 *
 * For now this is a safe no-op (no channels), so it satisfies
 * existing model hooks without sending real notifications.
 */
class MedicationOrderCreated extends Notification
{
    use Queueable;

    public function __construct(public MedicationOrder $order)
    {
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        // Intentionally return no channels for now.
        return [];
    }
}

