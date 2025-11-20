<?php

namespace App\Application\Commission\Queries;

use App\Application\Queries\QueryHandler;
use App\Domain\Shared\Queries\Query;
use App\Models\AgentCommission;
use Carbon\Carbon;
use InvalidArgumentException;

class GetAgentEarningsOverviewHandler implements QueryHandler
{
    public function handle(Query $query): array
    {
        if (! $query instanceof GetAgentEarningsOverview) {
            throw new InvalidArgumentException('GetAgentEarningsOverviewHandler can only handle GetAgentEarningsOverview queries.');
        }

        $startDate = $this->getStartDate($query->period);
        $endDate = now();

        // Get current period earnings
        $currentEarnings = AgentCommission::where('agent_id', $query->agentId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->sum('commission_amount');

        // Get previous period earnings for comparison
        $previousStartDate = $this->getPreviousStartDate($startDate, $query->period);
        $previousEarnings = AgentCommission::where('agent_id', $query->agentId)
            ->whereBetween('created_at', [$previousStartDate, $startDate])
            ->where('status', '!=', 'cancelled')
            ->sum('commission_amount');

        $change = $previousEarnings > 0 
            ? (($currentEarnings - $previousEarnings) / $previousEarnings) * 100 
            : 0;

        return [
            'current' => (float) $currentEarnings,
            'previous' => (float) $previousEarnings,
            'change' => round($change, 2),
            'period' => $query->period,
        ];
    }

    private function getStartDate(?string $period): Carbon
    {
        return match ($period) {
            'day' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };
    }

    private function getPreviousStartDate(Carbon $startDate, ?string $period): Carbon
    {
        return match ($period) {
            'day' => $startDate->copy()->subDay()->startOfDay(),
            'week' => $startDate->copy()->subWeek()->startOfWeek(),
            'year' => $startDate->copy()->subYear()->startOfYear(),
            default => $startDate->copy()->subMonth()->startOfMonth(),
        };
    }
}

