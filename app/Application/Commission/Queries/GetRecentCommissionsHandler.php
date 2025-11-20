<?php

namespace App\Application\Commission\Queries;

use App\Application\Queries\QueryHandler;
use App\Domain\Shared\Queries\Query;
use App\Models\AgentCommission;
use InvalidArgumentException;

class GetRecentCommissionsHandler implements QueryHandler
{
    public function handle(Query $query): array
    {
        if (! $query instanceof GetRecentCommissions) {
            throw new InvalidArgumentException('GetRecentCommissionsHandler can only handle GetRecentCommissions queries.');
        }

        $commissions = AgentCommission::where('agent_id', $query->agentId)
            ->where('status', '!=', 'cancelled')
            ->with(['subscription', 'transaction'])
            ->orderByDesc('created_at')
            ->paginate($query->limit, ['*'], 'page', $query->page);

        return [
            'data' => $commissions->items(),
            'total' => $commissions->total(),
            'per_page' => $commissions->perPage(),
            'current_page' => $commissions->currentPage(),
            'last_page' => $commissions->lastPage(),
        ];
    }
}

