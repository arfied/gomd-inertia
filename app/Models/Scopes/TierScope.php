<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TierScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Exclude LOA users from commission calculations by default
        $builder->whereHas('agent.user', function ($query) {
            $query->whereDoesntHave('roles', function ($roleQuery) {
                $roleQuery->where('name', 'loa');
            });
        });
    }

    /**
     * Extend the query builder with the needed functions.
     */
    public function extend(Builder $builder): void
    {
        $builder->macro('withLOA', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });

        $builder->macro('onlyLOA', function (Builder $builder) {
            return $builder->withoutGlobalScope($this)
                ->whereHas('agent.user', function ($query) {
                    $query->whereHas('roles', function ($roleQuery) {
                        $roleQuery->where('name', 'loa');
                    });
                });
        });

        $builder->macro('byTier', function (Builder $builder, string $tier) {
            return $builder->whereHas('agent', function ($query) use ($tier) {
                $query->where('tier', $tier);
            });
        });

        $builder->macro('eligibleForCommission', function (Builder $builder) {
            return $builder->whereHas('agent', function ($query) {
                $query->where('status', 'approved')
                    ->whereHas('user', function ($userQuery) {
                        $userQuery->where('role', '!=', 'loa')
                            ->where('status', 'active');
                    });
            });
        });

        $builder->macro('byTierHierarchy', function (Builder $builder, string $minTier = null, string $maxTier = null) {
            return $builder->whereHas('agent', function ($query) use ($minTier, $maxTier) {
                if ($minTier) {
                    $minHierarchy = \App\Models\Agent::TIER_HIERARCHY[$minTier] ?? 0;
                    $query->whereRaw('JSON_EXTRACT(?, CONCAT("$.", tier)) >= ?', [
                        json_encode(\App\Models\Agent::TIER_HIERARCHY),
                        $minHierarchy
                    ]);
                }

                if ($maxTier) {
                    $maxHierarchy = \App\Models\Agent::TIER_HIERARCHY[$maxTier] ?? 999;
                    $query->whereRaw('JSON_EXTRACT(?, CONCAT("$.", tier)) <= ?', [
                        json_encode(\App\Models\Agent::TIER_HIERARCHY),
                        $maxHierarchy
                    ]);
                }
            });
        });

        $builder->macro('withTierInfo', function (Builder $builder) {
            return $builder->with(['agent' => function ($query) {
                $query->select('id', 'user_id', 'tier', 'commission_rate', 'status')
                    ->with(['user:id,status,first_name,last_name']);
            }]);
        });

        $builder->macro('highTierOnly', function (Builder $builder) {
            return $builder->byTierHierarchy('MGA'); // MGA and above
        });

        $builder->macro('associateAndAgent', function (Builder $builder) {
            return $builder->whereHas('agent', function ($query) {
                $query->whereIn('tier', ['ASSOCIATE', 'AGENT']);
            });
        });

        $builder->macro('excludeAssociate', function (Builder $builder) {
            return $builder->whereHas('agent', function ($query) {
                $query->where('tier', '!=', 'ASSOCIATE');
            });
        });
    }
}


