<?php

namespace App\Application\Commission;

use App\Domain\Commission\Events\CommissionCancelled;
use App\Domain\Commission\Events\CommissionEarned;
use App\Models\AgentCommission;

/**
 * Eloquent implementation of Commission Dashboard projector.
 *
 * Projects commission events into the agent_commissions table
 * for dashboard queries and analytics.
 */
class EloquentCommissionDashboardProjector implements CommissionDashboardProjector
{
    public function projectCommissionEarned(CommissionEarned $event): void
    {
        AgentCommission::updateOrCreate(
            ['id' => $event->payload['commission_id'] ?? null],
            [
                'agent_id' => $event->payload['recipient_id'] ?? null,
                'upline_agent_id' => $event->payload['upline_agent_id'] ?? null,
                'transaction_id' => $event->payload['order_id'] ?? null,
                'subscription_id' => $event->payload['subscription_id'] ?? null,
                'total_amount' => $event->payload['order_total'] ?? 0,
                'commission_amount' => $event->payload['amount'] ?? 0,
                'upline_commission_amount' => $event->payload['upline_amount'] ?? 0,
                'agent_rate' => $event->payload['rate'] ?? 0,
                'upline_rate' => $event->payload['upline_rate'] ?? 0,
                'commission_frequency' => $event->payload['commission_frequency'] ?? 'monthly',
                'status' => 'pending',
                'created_at' => $event->occurredAt,
                'updated_at' => $event->occurredAt,
            ],
        );
    }

    public function projectCommissionCancelled(CommissionCancelled $event): void
    {
        $commission = AgentCommission::where(
            'id',
            $event->payload['commission_id'] ?? null
        )->first();

        if ($commission) {
            $commission->markAsCancelled();
        }
    }
}

