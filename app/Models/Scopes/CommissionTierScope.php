<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Commission-specific tier scope for AgentCommission model
 */
class CommissionTierScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Only include commissions for eligible agents (non-LOA)
        $builder->whereHas('agent', function ($query) {
            $query->where('status', 'approved')
                ->whereHas('user', function ($userQuery) {
                    $userQuery->where('status', 'active')
                        ->whereDoesntHave('roles', function ($roleQuery) {
                            $roleQuery->where('name', 'loa');
                        });
                });
        });
    }

    /**
     * Extend the query builder with commission-specific functions.
     */
    public function extend(Builder $builder): void
    {
        $builder->macro('includeAllAgents', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });

        $builder->macro('paidCommissions', function (Builder $builder) {
            return $builder->where('status', 'paid');
        });

        $builder->macro('pendingCommissions', function (Builder $builder) {
            return $builder->where('status', 'pending');
        });

        $builder->macro('forTier', function (Builder $builder, string $tier) {
            return $builder->whereHas('agent', function ($query) use ($tier) {
                $query->where('tier', $tier);
            });
        });

        $builder->macro('forPeriod', function (Builder $builder, $startDate, $endDate = null) {
            $endDate = $endDate ?: now();
            return $builder->whereBetween('created_at', [$startDate, $endDate]);
        });

        $builder->macro('withUplineCommissions', function (Builder $builder) {
            return $builder->with(['uplineAgent' => function ($query) {
                $query->select('id', 'user_id', 'tier', 'commission_rate')
                    ->with(['user:id,first_name,last_name']);
            }]);
        });

        $builder->macro('totalCommissionAmount', function (Builder $builder) {
            return $builder->sum('commission_amount');
        });

        $builder->macro('averageCommissionAmount', function (Builder $builder) {
            return $builder->avg('commission_amount');
        });

        $builder->macro('commissionsByTier', function (Builder $builder) {
            return $builder->select('agents.tier')
                ->selectRaw('COUNT(*) as commission_count')
                ->selectRaw('SUM(commission_amount) as total_amount')
                ->selectRaw('AVG(commission_amount) as average_amount')
                ->join('agents', 'agent_commissions.agent_id', '=', 'agents.id')
                ->groupBy('agents.tier')
                ->orderByRaw('FIELD(agents.tier, "ASSOCIATE", "AGENT", "MGA", "SVG", "FMO", "SFMO")');
        });

        $builder->macro('topEarners', function (Builder $builder, int $limit = 10) {
            return $builder->select('agent_id')
                ->selectRaw('SUM(commission_amount) as total_earnings')
                ->selectRaw('COUNT(*) as commission_count')
                ->with(['agent.user:id,first_name,last_name'])
                ->groupBy('agent_id')
                ->orderByDesc('total_earnings')
                ->limit($limit);
        });
    }
}
