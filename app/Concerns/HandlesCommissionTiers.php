<?php

namespace App\Concerns;

use App\Models\Agent;

trait HandlesCommissionTiers
{
    /**
     * Check if the user is eligible for commission calculations.
     * LOA role users are not eligible for commissions.
     *
     * @return bool
     */
    public function isEligibleForCommission(): bool
    {
        // LOA role users are not eligible for commissions
        if ($this->role === 'loa') {
            return false;
        }

        // Must have an agent profile to be eligible
        if (!$this->agent) {
            return false;
        }

        // Agent must be approved
        return $this->agent->status === 'approved';
    }

    /**
     * Check if the user is an LOA (referrer/encoder).
     *
     * @return bool
     */
    public function isLOA(): bool
    {
        return $this->hasRole('loa');
    }

    /**
     * Get the agent's commission tier.
     *
     * @return string|null
     */
    public function getCommissionTier(): ?string
    {
        if (!$this->agent) {
            return null;
        }

        return $this->agent->tier;
    }

    /**
     * Get the commission rate for the agent's tier.
     *
     * @param string $frequency Payment frequency (monthly, biannual, annual)
     * @return float
     */
    public function getCommissionRate(string $frequency = 'monthly'): float
    {
        if (!$this->isEligibleForCommission()) {
            return 0.0;
        }

        $tier = $this->getCommissionTier();

        if (!$tier) {
            return 0.0;
        }

        $rates = Agent::getCommissionRates($frequency);

        if (!isset($rates[$tier])) {
            return 0.0;
        }

        return (float) $rates[$tier];
    }

    /**
     * Check if the agent can be upgraded to a higher tier.
     *
     * @return bool
     */
    public function isEligibleForTierUpgrade(): bool
    {
        if (!$this->isEligibleForCommission()) {
            return false;
        }

        $currentTier = $this->getCommissionTier();

        if (!$currentTier) {
            return false;
        }

        // Can't upgrade if already at the highest tier
        $currentHierarchy = Agent::TIER_HIERARCHY[$currentTier] ?? -1;
        $maxHierarchy = max(Agent::TIER_HIERARCHY);

        return $currentHierarchy < $maxHierarchy;
    }

    /**
     * Get the next available tier for upgrade.
     *
     * @return string|null
     */
    public function getNextTier(): ?string
    {
        if (!$this->isEligibleForTierUpgrade()) {
            return null;
        }

        $currentTier = $this->getCommissionTier();
        $currentHierarchy = Agent::TIER_HIERARCHY[$currentTier] ?? -1;
        $nextHierarchy = $currentHierarchy + 1;

        foreach (Agent::TIER_HIERARCHY as $tier => $hierarchy) {
            if ($hierarchy === $nextHierarchy) {
                return $tier;
            }
        }

        return null;
    }

    /**
     * Check if the user can refer other agents.
     * LOA users can refer but don't earn commissions.
     *
     * @return bool
     */
    public function canReferAgents(): bool
    {
        return $this->role === 'agent' || $this->role === 'loa';
    }

    /**
     * Get tier-specific commission calculation rules.
     *
     * @return array
     */
    public function getTierCommissionRules(): array
    {
        $tier = $this->getCommissionTier();

        if (!$tier) {
            return [];
        }

        // Get tier rules from commission service
        $commissionService = app(\App\Services\CommissionService::class);
        $rules = $commissionService->getTierRules();

        return $rules[$tier] ?? [];
    }
}
