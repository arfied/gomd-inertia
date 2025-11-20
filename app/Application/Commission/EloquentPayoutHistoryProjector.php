<?php

namespace App\Application\Commission;

use App\Domain\Commission\Events\PayoutProcessed;
use App\Domain\Commission\Events\PayoutRequested;
use App\Models\AgentPayout;

/**
 * Eloquent implementation of Payout History projector.
 *
 * Projects payout events into the agent_payouts table
 * for payout history and tracking.
 */
class EloquentPayoutHistoryProjector implements PayoutHistoryProjector
{
    public function projectPayoutRequested(PayoutRequested $event): void
    {
        AgentPayout::updateOrCreate(
            ['id' => $event->payload['payout_id'] ?? null],
            [
                'agent_id' => $event->payload['agent_id'] ?? null,
                'total_amount' => $event->payload['total_amount'] ?? 0,
                'commission_count' => count($event->payload['commission_ids'] ?? []),
                'payout_method' => $event->payload['payout_method'] ?? 'bank_transfer',
                'status' => 'pending',
                'reference_number' => $event->payload['reference_number'] ?? AgentPayout::generateReferenceNumber(),
                'notes' => $event->payload['notes'] ?? null,
                'payment_details' => $event->payload['payment_details'] ?? null,
                'created_at' => $event->occurredAt,
                'updated_at' => $event->occurredAt,
            ],
        );
    }

    public function projectPayoutProcessed(PayoutProcessed $event): void
    {
        $payout = AgentPayout::where(
            'id',
            $event->payload['payout_id'] ?? null
        )->first();

        if ($payout) {
            $payout->markAsProcessed(
                $event->payload['processed_by'] ?? null,
                $event->payload['payment_reference'] ?? null
            );
        }
    }
}

