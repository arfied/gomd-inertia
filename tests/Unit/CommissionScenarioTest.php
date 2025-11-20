<?php

use App\Domain\Commission\CommissionCalculationEngine;

beforeEach(function () {
    $this->engine = new CommissionCalculationEngine();
});

describe('Commission Scenarios', function () {

    describe('Scenario 1: SFMO → SVG → Patient', function () {
        it('distributes commissions correctly', function () {
            $orderTotal = 100.00;
            $referralChain = ['svg_1', 'sfmo_1'];
            $agentTiers = [
                'svg_1' => 'svg',
                'sfmo_1' => 'sfmo',
            ];

            $commissions = $this->engine->calculateCommissionCascade(
                $orderTotal,
                $referralChain,
                $agentTiers,
                'monthly'
            );

            // Max commission = 55% (SFMO is highest)
            $totalAmount = array_sum(array_column($commissions, 'amount'));
            expect($totalAmount)->toBeLessThanOrEqual(55.00);

            // SVG should get 45% of their base rate
            $svgCommission = collect($commissions)->firstWhere('agent_id', 'svg_1');
            expect($svgCommission['tier'])->toBe('svg');

            // SFMO should get the remainder
            $sfmoCommission = collect($commissions)->firstWhere('agent_id', 'sfmo_1');
            expect($sfmoCommission['tier'])->toBe('sfmo');
        });
    });

    describe('Scenario 2: FMO → SVG → MGA → Agent → Patient', function () {
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

            // Max commission = 50% (FMO is highest)
            $totalAmount = array_sum(array_column($commissions, 'amount'));
            expect($totalAmount)->toBeLessThanOrEqual(50.01);

            // Difference-based distribution (from highest tier downward):
            // FMO (50%) - 0% = 50%
            // SVG (45%) - 0% = 45% (but capped at 50% total, so 5%)
            // MGA (40%) - 0% = 40% (but capped at 50% total, so 10%)
            // Agent (30%) - 0% = 30%
            // Results reversed to show closest to patient first:
            expect($commissions)->toHaveCount(4);

            // Verify order (closest to patient first)
            $agentIds = array_column($commissions, 'agent_id');
            expect($agentIds)->toBe(['agent_1', 'mga_1', 'svg_1', 'fmo_1']);

            // Verify amounts
            expect($commissions[0]['amount'])->toBe(30.00); // Agent: 30% - 0% = 30%
            expect($commissions[1]['amount'])->toBe(10.00); // MGA: 40% - 30% = 10%
            expect($commissions[2]['amount'])->toBe(5.00);  // SVG: 45% - 40% = 5%
            expect($commissions[3]['amount'])->toBe(5.00);  // FMO: 50% - 45% = 5%
        });
    });

    describe('Associate as highest tier', function () {
        it('allows associate to receive 20% commission when highest', function () {
            $orderTotal = 100.00;
            $referralChain = ['associate_1'];
            $agentTiers = ['associate_1' => 'associate'];

            $commissions = $this->engine->calculateCommissionCascade(
                $orderTotal,
                $referralChain,
                $agentTiers,
                'monthly'
            );

            expect($commissions)->toHaveCount(1);
            expect($commissions[0]['rate'])->toBe(20.0);
            expect($commissions[0]['amount'])->toBe(20.00);
        });
    });

    describe('LOA handling', function () {
        it('never includes LOA in commissions', function () {
            $orderTotal = 100.00;
            $referralChain = ['loa_1', 'associate_1', 'agent_1'];
            $agentTiers = [
                'loa_1' => 'loa',
                'associate_1' => 'associate',
                'agent_1' => 'agent',
            ];

            $commissions = $this->engine->calculateCommissionCascade(
                $orderTotal,
                $referralChain,
                $agentTiers,
                'monthly'
            );

            // LOA should be filtered out
            $agentIds = array_column($commissions, 'agent_id');
            expect($agentIds)->not->toContain('loa_1');
            expect($agentIds)->toContain('associate_1', 'agent_1');
        });
    });

    describe('Commission output format', function () {
        it('returns correct output structure', function () {
            $orderTotal = 100.00;
            $referralChain = ['agent_1', 'mga_1'];
            $agentTiers = [
                'agent_1' => 'agent',
                'mga_1' => 'mga',
            ];

            $commissions = $this->engine->calculateCommissionCascade(
                $orderTotal,
                $referralChain,
                $agentTiers,
                'monthly'
            );

            $commission = $commissions[0];
            expect($commission)->toHaveKeys([
                'agent_id',
                'tier',
                'rate',
                'amount',
                'order_total',
                'frequency',
            ]);
        });
    });
});

