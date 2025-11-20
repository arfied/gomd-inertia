<?php

use App\Domain\Commission\CommissionCalculationEngine;

beforeEach(function () {
    $this->engine = new CommissionCalculationEngine();
});

describe('Commission Edge Cases', function () {

    describe('Tier validation', function () {
        it('excludes LOA from valid tiers', function () {
            $tiers = $this->engine->getValidTiers();
            expect($tiers)->not->toContain('loa');
        });

        it('validates all commission-eligible tiers', function () {
            $validTiers = ['associate', 'agent', 'mga', 'svg', 'fmo', 'sfmo'];
            foreach ($validTiers as $tier) {
                expect($this->engine->isValidTier($tier))->toBeTrue();
            }
        });
    });

    describe('Frequency validation', function () {
        it('validates all supported frequencies', function () {
            expect($this->engine->isValidFrequency('monthly'))->toBeTrue();
            expect($this->engine->isValidFrequency('biannual'))->toBeTrue();
            expect($this->engine->isValidFrequency('annual'))->toBeTrue();
        });

        it('rejects invalid frequencies', function () {
            expect($this->engine->isValidFrequency('weekly'))->toBeFalse();
            expect($this->engine->isValidFrequency('quarterly'))->toBeFalse();
        });
    });

    describe('Sequential distribution accuracy', function () {
        it('maintains total commission within cap', function () {
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
            // SFMO is highest, cap is 55%
            expect($totalAmount)->toBeLessThanOrEqual(55.01); // Allow small rounding
        });

        it('stops distributing when cap is reached', function () {
            $orderTotal = 100.00;
            $referralChain = ['agent_1', 'mga_1', 'svg_1'];
            $agentTiers = [
                'agent_1' => 'agent',
                'mga_1' => 'mga',
                'svg_1' => 'svg',
            ];

            $commissions = $this->engine->calculateCommissionCascade(
                $orderTotal,
                $referralChain,
                $agentTiers,
                'monthly'
            );

            // SVG is highest, cap is 45%
            // Difference-based: agent=30%, mga=10%, svg=5%
            expect($commissions)->toHaveCount(3);
            expect($commissions[0]['agent_id'])->toBe('agent_1');
            expect($commissions[0]['rate'])->toBe(30.0);
            expect($commissions[1]['agent_id'])->toBe('mga_1');
            expect($commissions[1]['rate'])->toBe(10.0); // 40% - 30% = 10%
            expect($commissions[2]['agent_id'])->toBe('svg_1');
            expect($commissions[2]['rate'])->toBe(5.0); // 45% - 40% = 5%
        });
    });

    describe('Mixed valid and invalid agents', function () {
        it('filters invalid tiers while keeping valid ones', function () {
            $orderTotal = 100.00;
            $referralChain = ['invalid_agent', 'agent_1', 'mga_1'];
            $agentTiers = [
                'invalid_agent' => 'invalid_tier',
                'agent_1' => 'agent',
                'mga_1' => 'mga',
            ];

            $commissions = $this->engine->calculateCommissionCascade(
                $orderTotal,
                $referralChain,
                $agentTiers,
                'monthly'
            );

            expect($commissions)->toHaveCount(2);
            $agentIds = array_column($commissions, 'agent_id');
            expect($agentIds)->toContain('agent_1', 'mga_1');
            expect($agentIds)->not->toContain('invalid_agent');
        });
    });

    describe('Different subscription amounts', function () {
        it('scales commissions correctly for different amounts', function () {
            $referralChain = ['agent_1'];
            $agentTiers = ['agent_1' => 'agent'];

            $commissions100 = $this->engine->calculateCommissionCascade(
                100.00,
                $referralChain,
                $agentTiers,
                'monthly'
            );

            $commissions200 = $this->engine->calculateCommissionCascade(
                200.00,
                $referralChain,
                $agentTiers,
                'monthly'
            );

            // Rate should be the same, amount should double
            expect($commissions100[0]['rate'])->toBe($commissions200[0]['rate']);
            expect($commissions200[0]['amount'])->toBe($commissions100[0]['amount'] * 2);
        });
    });

    describe('Highest tier detection', function () {
        it('correctly identifies highest tier and applies cap', function () {
            $orderTotal = 100.00;
            $referralChain = ['agent_1', 'svg_1', 'mga_1', 'fmo_1'];
            $agentTiers = [
                'agent_1' => 'agent',
                'svg_1' => 'svg',
                'mga_1' => 'mga',
                'fmo_1' => 'fmo',
            ];

            $commissions = $this->engine->calculateCommissionCascade(
                $orderTotal,
                $referralChain,
                $agentTiers,
                'monthly'
            );

            // FMO is highest, so cap should be 50%
            $totalAmount = array_sum(array_column($commissions, 'amount'));
            expect($totalAmount)->toBeLessThanOrEqual(50.01);

            // Difference-based: agent=30%, mga=10%, svg=5%, fmo=5%
            expect($commissions)->toHaveCount(4);
            expect($commissions[0]['agent_id'])->toBe('agent_1');
            expect($commissions[0]['rate'])->toBe(30.0);
            expect($commissions[1]['agent_id'])->toBe('mga_1');
            expect($commissions[1]['rate'])->toBe(10.0); // 40% - 30% = 10%
            expect($commissions[2]['agent_id'])->toBe('svg_1');
            expect($commissions[2]['rate'])->toBe(5.0); // 45% - 40% = 5%
            expect($commissions[3]['agent_id'])->toBe('fmo_1');
            expect($commissions[3]['rate'])->toBe(5.0); // 50% - 45% = 5%
        });
    });

    describe('Output consistency', function () {
        it('returns consistent structure for all commissions', function () {
            $orderTotal = 100.00;
            $referralChain = ['agent_1', 'mga_1', 'svg_1'];
            $agentTiers = [
                'agent_1' => 'agent',
                'mga_1' => 'mga',
                'svg_1' => 'svg',
            ];

            $commissions = $this->engine->calculateCommissionCascade(
                $orderTotal,
                $referralChain,
                $agentTiers,
                'monthly'
            );

            foreach ($commissions as $commission) {
                expect($commission)->toHaveKeys([
                    'agent_id',
                    'tier',
                    'rate',
                    'amount',
                    'order_total',
                    'frequency',
                ]);
                expect($commission['amount'])->toBeGreaterThanOrEqual(0);
                expect($commission['rate'])->toBeGreaterThanOrEqual(0);
            }
        });
    });
});

