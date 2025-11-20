<?php

namespace App\Application\Commission\Queries;

use App\Application\Queries\QueryHandler;
use App\Domain\Shared\Queries\Query;
use App\Models\Agent;
use InvalidArgumentException;

class GetAgentReferralHierarchyHandler implements QueryHandler
{
    public function handle(Query $query): array
    {
        if (! $query instanceof GetAgentReferralHierarchy) {
            throw new InvalidArgumentException('GetAgentReferralHierarchyHandler can only handle GetAgentReferralHierarchy queries.');
        }

        $agent = Agent::find($query->agentId);
        if (!$agent) {
            return [];
        }

        return [
            'upline' => $this->buildUplineHierarchy($agent, $query->depth),
            'downline' => $this->buildDownlineHierarchy($agent, $query->depth),
        ];
    }

    private function buildUplineHierarchy(Agent $agent, int $depth, int $currentDepth = 0): array
    {
        if ($currentDepth >= $depth || !$agent->referrer) {
            return [];
        }

        $referrer = $agent->referrer;

        return [
            'id' => $referrer->id,
            'name' => $referrer->user?->name ?? 'Unknown',
            'tier' => $referrer->tier,
            'status' => $referrer->status,
            'parent' => $this->buildUplineHierarchy($referrer, $depth, $currentDepth + 1),
        ];
    }

    private function buildDownlineHierarchy(Agent $agent, int $depth, int $currentDepth = 0): array
    {
        if ($currentDepth >= $depth) {
            return [];
        }

        $referrals = $agent->referrals()->get();

        return $referrals->map(function (Agent $referral) use ($depth, $currentDepth) {
            return [
                'id' => $referral->id,
                'name' => $referral->user?->name ?? 'Unknown',
                'tier' => $referral->tier,
                'status' => $referral->status,
                'children' => $this->buildDownlineHierarchy($referral, $depth, $currentDepth + 1),
            ];
        })->toArray();
    }
}

