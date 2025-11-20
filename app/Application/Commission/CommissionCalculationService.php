<?php

namespace App\Application\Commission;

use App\Domain\Commission\CommissionCalculationEngine;
use App\Models\User;

/**
 * High-level service for calculating commissions for a patient/user.
 *
 * Orchestrates the referral chain building and commission calculation.
 * This is the primary service to use for commission calculations.
 *
 * Usage:
 * $service = new CommissionCalculationService();
 * $commissions = $service->calculateForUser($user, $orderTotal, $frequency);
 */
class CommissionCalculationService
{
    private ReferralChainBuilder $chainBuilder;
    private CommissionCalculationEngine $engine;

    public function __construct(
        ?ReferralChainBuilder $chainBuilder = null,
        ?CommissionCalculationEngine $engine = null
    ) {
        $this->chainBuilder = $chainBuilder ?? new ReferralChainBuilder();
        $this->engine = $engine ?? new CommissionCalculationEngine();
    }

    /**
     * Calculate commissions for a user's subscription.
     *
     * @param User $user The patient/user
     * @param float $orderTotal The subscription amount
     * @param string $frequency The subscription frequency (monthly, biannual, annual)
     * @return array Array of commission calculations
     */
    public function calculateForUser(
        User $user,
        float $orderTotal,
        string $frequency = 'monthly'
    ): array {
        // Build the referral chain
        $referralData = $this->chainBuilder->buildCompleteReferralData($user);

        // Calculate commissions
        return $this->engine->calculateCommissionCascade(
            $orderTotal,
            $referralData['chain'],
            $referralData['tiers'],
            $frequency
        );
    }

    /**
     * Calculate commissions with explicit chain and tiers.
     *
     * Useful for testing or when you already have the chain built.
     *
     * @param array $referralChain Array of agent IDs
     * @param array $agentTiers Map of agent ID to tier
     * @param float $orderTotal The subscription amount
     * @param string $frequency The subscription frequency
     * @return array Array of commission calculations
     */
    public function calculateWithChain(
        array $referralChain,
        array $agentTiers,
        float $orderTotal,
        string $frequency = 'monthly'
    ): array {
        return $this->engine->calculateCommissionCascade(
            $orderTotal,
            $referralChain,
            $agentTiers,
            $frequency
        );
    }

    /**
     * Get the referral chain for a user without calculating commissions.
     *
     * @param User $user The patient/user
     * @return array ['chain' => [...], 'tiers' => [...]]
     */
    public function getReferralData(User $user): array
    {
        return $this->chainBuilder->buildCompleteReferralData($user);
    }

    /**
     * Get the underlying chain builder.
     *
     * @return ReferralChainBuilder
     */
    public function getChainBuilder(): ReferralChainBuilder
    {
        return $this->chainBuilder;
    }

    /**
     * Get the underlying calculation engine.
     *
     * @return CommissionCalculationEngine
     */
    public function getEngine(): CommissionCalculationEngine
    {
        return $this->engine;
    }
}

