<?php

namespace App\Application\Commission;

use App\Models\Agent;
use App\Models\User;

/**
 * Builds the referral chain for commission calculations.
 *
 * Traces from a patient/user upward through the referral hierarchy,
 * automatically skipping LOA agents and returning only commission-eligible agents.
 *
 * Data Flow:
 * Patient → referring_loa_id (LOA) → referring_agent_id (Agent) → ... (upline)
 *
 * Returns: [agent_id, mga_id, svg_id, fmo_id, sfmo_id] (LOA skipped)
 */
class ReferralChainBuilder
{
    /**
     * Build the referral chain for a patient/user.
     *
     * @param User $user The patient/user to build chain for
     * @return array Array of agent IDs from lowest (patient referrer) to highest
     */
    public function buildChainForUser(User $user): array
    {
        $chain = [];
        $visited = [];

        // Start with the user's direct agent referrer
        $currentAgent = $user->referringAgent;

        // If no direct agent referrer, check if referred by LOA
        if (!$currentAgent && $user->referring_loa_id) {
            $loa = User::find($user->referring_loa_id);
            if ($loa && $loa->referring_agent_id) {
                $currentAgent = Agent::find($loa->referring_agent_id);
            }
        }

        // Build chain upward through the hierarchy
        while ($currentAgent) {
            // Prevent infinite loops
            if (isset($visited[$currentAgent->id])) {
                break;
            }
            $visited[$currentAgent->id] = true;

            $chain[] = $currentAgent->id;

            // Move to the next agent in the hierarchy
            $currentAgent = $currentAgent->referrer;
        }

        return $chain;
    }

    /**
     * Build the referral chain for an agent.
     *
     * @param Agent $agent The agent to build chain for
     * @return array Array of agent IDs from the agent upward to top
     */
    public function buildChainForAgent(Agent $agent): array
    {
        $chain = [];
        $visited = [];
        $currentAgent = $agent;

        while ($currentAgent) {
            // Prevent infinite loops
            if (isset($visited[$currentAgent->id])) {
                break;
            }
            $visited[$currentAgent->id] = true;

            $chain[] = $currentAgent->id;

            // Move to the next agent in the hierarchy
            $currentAgent = $currentAgent->referrer;
        }

        return $chain;
    }

    /**
     * Get agent tiers for a referral chain.
     *
     * @param array $agentIds Array of agent IDs
     * @return array Map of agent ID to tier
     */
    public function getAgentTiers(array $agentIds): array
    {
        if (empty($agentIds)) {
            return [];
        }

        $agents = Agent::whereIn('id', $agentIds)
            ->select('id', 'tier')
            ->get();

        $tiers = [];
        foreach ($agents as $agent) {
            $tiers[$agent->id] = strtolower($agent->tier);
        }

        return $tiers;
    }

    /**
     * Build complete referral data for commission calculation.
     *
     * @param User $user The patient/user
     * @return array ['chain' => [...], 'tiers' => [...]]
     */
    public function buildCompleteReferralData(User $user): array
    {
        $chain = $this->buildChainForUser($user);
        $tiers = $this->getAgentTiers($chain);

        return [
            'chain' => $chain,
            'tiers' => $tiers,
        ];
    }
}

