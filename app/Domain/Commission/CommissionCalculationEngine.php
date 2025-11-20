<?php

namespace App\Domain\Commission;

/**
 * Commission Calculation Engine
 *
 * Implements hierarchical commission cascade logic for subscription-based sales.
 * Commissions are distributed through the referral chain up to a maximum cap
 * determined by the highest tier in the chain.
 */
class CommissionCalculationEngine
{
    /**
     * Commission rates by tier and frequency.
     * These are the base rates for each tier when they are the highest in the chain.
     */
    public const COMMISSION_RATES = [
        'monthly' => [
            'sfmo' => 55.0,
            'fmo' => 50.0,
            'svg' => 45.0,
            'mga' => 40.0,
            'agent' => 30.0,
            'associate' => 20.0,
        ],
        'biannual' => [
            'sfmo' => 27.5,
            'fmo' => 25.0,
            'svg' => 23.0,
            'mga' => 20.0,
            'agent' => 15.0,
            'associate' => 14.0,
        ],
        'annual' => [
            'sfmo' => 27.5,
            'fmo' => 25.0,
            'svg' => 23.0,
            'mga' => 20.0,
            'agent' => 15.0,
            'associate' => 14.0,
        ],
    ];

    /**
     * Hierarchy order from lowest to highest tier.
     * LOA is not included as they never receive commissions.
     */
    public const TIER_HIERARCHY = [
        'associate',
        'agent',
        'mga',
        'svg',
        'fmo',
        'sfmo',
    ];

    /**
     * Maximum commission caps by highest tier in the referral chain.
     */
    public const COMMISSION_CAPS = [
        'sfmo' => 55.0,
        'fmo' => 50.0,
        'svg' => 45.0,
        'mga' => 40.0,
        'agent' => 30.0,
        'associate' => 20.0,
    ];

    /**
     * Calculate commission cascade through the referral hierarchy.
     *
     * Distributes commissions using difference-based allocation.
     * Each agent receives the difference between their tier rate and the previous tier's rate.
     * Processing occurs from highest to lowest tier, then results are reversed to show
     * agents from closest to patient first.
     *
     * @param float $orderTotal The subscription amount
     * @param array $referralChain Array of agent IDs from lowest (patient referrer) to highest
     * @param array $agentTiers Map of agent ID to their tier
     * @param string $frequency The subscription frequency (monthly, biannual, annual)
     * @return array Array of commission calculations for each eligible agent
     */
    public function calculateCommissionCascade(
        float $orderTotal,
        array $referralChain,
        array $agentTiers,
        string $frequency = 'monthly'
    ): array {
        if (empty($referralChain) || ! $this->isValidFrequency($frequency)) {
            return [];
        }

        // Filter out LOA agents and invalid tiers
        $eligibleAgents = $this->filterEligibleAgents($referralChain, $agentTiers);

        if (empty($eligibleAgents)) {
            return [];
        }

        // Find the highest tier in the chain to determine the cap
        $highestTier = $this->getHighestTier($eligibleAgents, $agentTiers);
        $maximumCap = self::COMMISSION_CAPS[$highestTier] ?? 0;

        if ($maximumCap === 0) {
            return [];
        }

        // Distribute commissions using difference-based allocation
        return $this->distributeCommissionsByChain(
            $eligibleAgents,
            $agentTiers,
            $frequency,
            $orderTotal,
            $maximumCap
        );
    }

    /**
     * Filter eligible agents from the referral chain.
     * Removes LOA agents and invalid tiers.
     */
    private function filterEligibleAgents(array $referralChain, array $agentTiers): array
    {
        $eligible = [];

        foreach ($referralChain as $agentId) {
            $tier = $agentTiers[$agentId] ?? null;

            // Skip LOA agents and invalid tiers
            if ($tier === 'loa' || ! $this->isValidTier($tier)) {
                continue;
            }

            $eligible[] = $agentId;
        }

        return $eligible;
    }

    /**
     * Get the highest tier in the eligible agents list.
     */
    private function getHighestTier(array $eligibleAgents, array $agentTiers): ?string
    {
        $highestIndex = -1;
        $highestTier = null;

        foreach ($eligibleAgents as $agentId) {
            $tier = $agentTiers[$agentId] ?? null;
            $tierIndex = array_search($tier, self::TIER_HIERARCHY, true);

            if ($tierIndex > $highestIndex) {
                $highestIndex = $tierIndex;
                $highestTier = $tier;
            }
        }

        return $highestTier;
    }

    /**
     * Distribute commissions using difference-based allocation.
     *
     * Processes agents from lowest to highest tier, calculating the difference
     * between each tier's rate and the previous tier's rate.
     * Results are already in order from closest to patient first.
     *
     * Example: FMO → SVG → MGA → Agent → Patient
     * - Agent (30%): 30% - 0% = 30%
     * - MGA (40%): 40% - 30% = 10%
     * - SVG (45%): 45% - 40% = 5%
     * - FMO (50%): 50% - 45% = 5%
     * Total: 50% (FMO cap)
     */
    private function distributeCommissionsByChain(
        array $eligibleAgents,
        array $agentTiers,
        string $frequency,
        float $orderTotal,
        float $maximumCap
    ): array {
        $rates = self::COMMISSION_RATES[$frequency] ?? self::COMMISSION_RATES['monthly'];

        // Sort agents by tier hierarchy (lowest to highest)
        $sortedAgents = $this->sortAgentsByTierAscending($eligibleAgents, $agentTiers);

        // Calculate difference-based commissions
        $commissions = [];
        $previousRate = 0;
        $remainingCap = $maximumCap;

        foreach ($sortedAgents as $agentId) {
            $tier = $agentTiers[$agentId];
            $currentRate = $rates[$tier] ?? 0;

            // Calculate the difference between current and previous tier rate
            $differenceRate = $currentRate - $previousRate;

            // Ensure we don't exceed the remaining cap
            if ($differenceRate > $remainingCap) {
                $differenceRate = $remainingCap;
            }

            // Skip if no commission to allocate
            if ($differenceRate <= 0) {
                continue;
            }

            $amount = ($orderTotal * $differenceRate) / 100;

            $commissions[] = [
                'agent_id' => $agentId,
                'tier' => $tier,
                'rate' => round($differenceRate, 2),
                'amount' => round($amount, 2),
                'order_total' => $orderTotal,
                'frequency' => $frequency,
            ];

            $previousRate = $currentRate;
            $remainingCap -= $differenceRate;
        }

        return $commissions;
    }

    /**
     * Sort agents by tier hierarchy in ascending order (lowest to highest).
     */
    private function sortAgentsByTierAscending(array $agentIds, array $agentTiers): array
    {
        $agentsWithTierIndex = [];

        foreach ($agentIds as $agentId) {
            $tier = $agentTiers[$agentId];
            $tierIndex = array_search($tier, self::TIER_HIERARCHY, true);
            $agentsWithTierIndex[] = [
                'agent_id' => $agentId,
                'tier_index' => $tierIndex,
            ];
        }

        // Sort by tier index in ascending order (lowest tier first)
        usort($agentsWithTierIndex, function ($a, $b) {
            return $a['tier_index'] <=> $b['tier_index'];
        });

        return array_map(function ($item) {
            return $item['agent_id'];
        }, $agentsWithTierIndex);
    }

    /**
     * Get commission rate for a specific tier and frequency.
     */
    public function getCommissionRate(string $tier, string $frequency = 'monthly'): float
    {
        $rates = self::COMMISSION_RATES[$frequency] ?? self::COMMISSION_RATES['monthly'];
        return (float) ($rates[$tier] ?? 0);
    }

    /**
     * Validate if a tier is valid.
     */
    public function isValidTier(string $tier): bool
    {
        return in_array($tier, self::TIER_HIERARCHY);
    }

    /**
     * Validate if a frequency is valid.
     */
    public function isValidFrequency(string $frequency): bool
    {
        return isset(self::COMMISSION_RATES[$frequency]);
    }

    /**
     * Get all valid tiers.
     */
    public function getValidTiers(): array
    {
        return self::TIER_HIERARCHY;
    }

    /**
     * Get all valid frequencies.
     */
    public function getValidFrequencies(): array
    {
        return array_keys(self::COMMISSION_RATES);
    }
}

