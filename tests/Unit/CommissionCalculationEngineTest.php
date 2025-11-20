<?php

use App\Domain\Commission\CommissionCalculationEngine;

beforeEach(function () {
    $this->engine = new CommissionCalculationEngine();
});

describe('CommissionCalculationEngine', function () {

    describe('calculateCommissionCascade', function () {
        it('filters out LOA agents from referral chain', function () {
            $orderTotal = 100.00;
            $referralChain = ['loa_1', 'agent_1', 'mga_1'];
            $agentTiers = [
                'loa_1' => 'loa',
                'agent_1' => 'agent',
                'mga_1' => 'mga',
            ];

            $commissions = $this->engine->calculateCommissionCascade(
                $orderTotal,
                $referralChain,
                $agentTiers,
                'monthly'
            );

            // LOA should be filtered out
            expect($commissions)->toHaveCount(2);
            expect($commissions[0]['agent_id'])->toBe('agent_1');
            expect($commissions[1]['agent_id'])->toBe('mga_1');
        });

        it('respects maximum cap based on highest tier (SFMO)', function () {
            $orderTotal = 100.00;
            $referralChain = ['agent_1', 'mga_1', 'svg_1', 'fmo_1', 'sfmo_1'];
            $agentTiers = [
                'agent_1' => 'agent',
                'mga_1' => 'mga',
                'svg_1' => 'svg',
                'fmo_1' => 'fmo',
                'sfmo_1' => 'sfmo',
            ];

            $commissions = $this->engine->calculateCommissionCascade(
                $orderTotal,
                $referralChain,
                $agentTiers,
                'monthly'
            );

            $totalAmount = array_sum(array_column($commissions, 'amount'));
            // Max cap for SFMO is 55%
            expect($totalAmount)->toBeLessThanOrEqual(55.00);
        });

        it('distributes commissions using difference-based allocation', function () {
            $orderTotal = 100.00;
            $referralChain = ['agent_1', 'mga_1', 'svg_1', 'fmo_1'];
            $agentTiers = [
                'agent_1' => 'agent',
                'mga_1' => 'mga',
                'svg_1' => 'svg',
                'fmo_1' => 'fmo',
            ];

            $commissions = $this->engine->calculateCommissionCascade(
                $orderTotal,
                $referralChain,
                $agentTiers,
                'monthly'
            );

            // Max cap for FMO is 50%
            $totalAmount = array_sum(array_column($commissions, 'amount'));
            expect($totalAmount)->toBeLessThanOrEqual(50.01); // Allow small rounding

            // Difference-based distribution (from highest tier downward):
            // FMO (50%) - 0% = 50%
            // SVG (45%) - 0% = 45% (but capped at 50% total, so 5%)
            // MGA (40%) - 0% = 40% (but capped at 50% total, so 10%)
            // Agent (30%) - 0% = 30%
            // Results reversed to show closest to patient first:
            expect($commissions[0]['agent_id'])->toBe('agent_1');
            expect($commissions[0]['rate'])->toBe(30.0);
            expect($commissions[0]['amount'])->toBe(30.00);

            expect($commissions[1]['agent_id'])->toBe('mga_1');
            expect($commissions[1]['rate'])->toBe(10.0); // 40% - 30% = 10%
            expect($commissions[1]['amount'])->toBe(10.00);

            expect($commissions[2]['agent_id'])->toBe('svg_1');
            expect($commissions[2]['rate'])->toBe(5.0); // 45% - 40% = 5%
            expect($commissions[2]['amount'])->toBe(5.00);

            expect($commissions[3]['agent_id'])->toBe('fmo_1');
            expect($commissions[3]['rate'])->toBe(5.0); // 50% - 45% = 5%
            expect($commissions[3]['amount'])->toBe(5.00);

            // All 4 agents get paid
            expect($commissions)->toHaveCount(4);
        });

        it('handles single agent in chain', function () {
            $orderTotal = 100.00;
            $referralChain = ['agent_1'];
            $agentTiers = ['agent_1' => 'agent'];

            $commissions = $this->engine->calculateCommissionCascade(
                $orderTotal,
                $referralChain,
                $agentTiers,
                'monthly'
            );

            expect($commissions)->toHaveCount(1);
            expect($commissions[0]['agent_id'])->toBe('agent_1');
            expect($commissions[0]['tier'])->toBe('agent');
            expect($commissions[0]['rate'])->toBe(30.0);
            expect($commissions[0]['amount'])->toBe(30.00);
        });

        it('handles empty referral chain', function () {
            $commissions = $this->engine->calculateCommissionCascade(
                100.00,
                [],
                [],
                'monthly'
            );

            expect($commissions)->toBeEmpty();
        });

        it('returns empty array when all agents are LOA', function () {
            $orderTotal = 100.00;
            $referralChain = ['loa_1', 'loa_2'];
            $agentTiers = [
                'loa_1' => 'loa',
                'loa_2' => 'loa',
            ];

            $commissions = $this->engine->calculateCommissionCascade(
                $orderTotal,
                $referralChain,
                $agentTiers,
                'monthly'
            );

            expect($commissions)->toBeEmpty();
        });

        it('applies biannual frequency correctly', function () {
            $orderTotal = 100.00;
            $referralChain = ['agent_1'];
            $agentTiers = ['agent_1' => 'agent'];

            $commissions = $this->engine->calculateCommissionCascade(
                $orderTotal,
                $referralChain,
                $agentTiers,
                'biannual'
            );

            expect($commissions[0]['rate'])->toBe(15.0);
            expect($commissions[0]['amount'])->toBe(15.00);
        });

        it('applies annual frequency correctly', function () {
            $orderTotal = 100.00;
            $referralChain = ['sfmo_1'];
            $agentTiers = ['sfmo_1' => 'sfmo'];

            $commissions = $this->engine->calculateCommissionCascade(
                $orderTotal,
                $referralChain,
                $agentTiers,
                'annual'
            );

            expect($commissions[0]['rate'])->toBe(27.5);
            expect($commissions[0]['amount'])->toBe(27.50);
        });
    });

    describe('getCommissionRate', function () {
        it('returns correct rate for tier and frequency', function () {
            $rate = $this->engine->getCommissionRate('agent', 'monthly');
            expect($rate)->toBe(30.0);

            $rate = $this->engine->getCommissionRate('sfmo', 'monthly');
            expect($rate)->toBe(55.0);

            $rate = $this->engine->getCommissionRate('agent', 'biannual');
            expect($rate)->toBe(15.0);
        });

        it('returns zero for invalid tier', function () {
            $rate = $this->engine->getCommissionRate('invalid_tier', 'monthly');
            expect($rate)->toBe(0.0);
        });
    });

    describe('isValidTier', function () {
        it('validates tier correctly', function () {
            expect($this->engine->isValidTier('agent'))->toBeTrue();
            expect($this->engine->isValidTier('sfmo'))->toBeTrue();
            expect($this->engine->isValidTier('associate'))->toBeTrue();
            expect($this->engine->isValidTier('invalid'))->toBeFalse();
        });

        it('does not include LOA in valid tiers', function () {
            expect($this->engine->isValidTier('loa'))->toBeFalse();
        });
    });

    describe('getValidTiers', function () {
        it('returns all valid tiers excluding LOA', function () {
            $tiers = $this->engine->getValidTiers();
            expect($tiers)->toContain('agent', 'sfmo', 'mga', 'associate');
            expect($tiers)->not->toContain('loa');
        });
    });

    describe('getValidFrequencies', function () {
        it('returns all valid frequencies', function () {
            $frequencies = $this->engine->getValidFrequencies();
            expect($frequencies)->toContain('monthly', 'biannual', 'annual');
        });
    });
});

