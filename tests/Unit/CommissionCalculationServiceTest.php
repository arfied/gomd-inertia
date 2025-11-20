<?php

use App\Application\Commission\CommissionCalculationService;
use App\Application\Commission\ReferralChainBuilder;
use App\Domain\Commission\CommissionCalculationEngine;
use App\Models\Agent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

describe('CommissionCalculationService', function () {
    uses(RefreshDatabase::class);
    describe('calculateForUser', function () {
        it('calculates commissions for user with referral chain', function () {
            $agent = Agent::factory()->create(['tier' => 'AGENT']);
            $user = User::factory()->create(['referring_agent_id' => $agent->id]);

            $service = new CommissionCalculationService();
            $commissions = $service->calculateForUser($user, 100.00, 'monthly');

            expect($commissions)->not->toBeEmpty();
            expect($commissions[0]['agent_id'])->toBe($agent->id);
            expect($commissions[0]['amount'])->toBe(30.00);
        });

        it('calculates commissions through multiple agent levels', function () {
            $sfmo = Agent::factory()->create(['tier' => 'SFMO']);
            $fmo = Agent::factory()->create(['tier' => 'FMO', 'referring_agent_id' => $sfmo->id]);
            $agent = Agent::factory()->create(['tier' => 'AGENT', 'referring_agent_id' => $fmo->id]);
            $user = User::factory()->create(['referring_agent_id' => $agent->id]);

            $service = new CommissionCalculationService();
            $commissions = $service->calculateForUser($user, 100.00, 'monthly');

            // Difference-based distribution with SFMO cap (55%):
            // SFMO (55%) - 0% = 55%
            // FMO (50%) - 0% = 50% (but capped at 55% total, so 5%)
            // Agent (30%) - 0% = 30%
            // Results reversed to show closest to patient first:
            expect($commissions)->toHaveCount(3);
            expect($commissions[0]['agent_id'])->toBe($agent->id);
            expect($commissions[0]['amount'])->toBe(30.00); // Agent: 30% - 0% = 30%
            expect($commissions[1]['agent_id'])->toBe($fmo->id);
            expect($commissions[1]['amount'])->toBe(20.00); // FMO: 50% - 30% = 20%
            expect($commissions[2]['agent_id'])->toBe($sfmo->id);
            expect($commissions[2]['amount'])->toBe(5.00);  // SFMO: 55% - 50% = 5%
        });

        it('returns empty array for user with no referrer', function () {
            $user = User::factory()->create([
                'referring_agent_id' => null,
                'referring_loa_id' => null,
            ]);

            $service = new CommissionCalculationService();
            $commissions = $service->calculateForUser($user, 100.00, 'monthly');

            expect($commissions)->toBeEmpty();
        });

        it('applies correct frequency rates', function () {
            $agent = Agent::factory()->create(['tier' => 'AGENT']);
            $user = User::factory()->create(['referring_agent_id' => $agent->id]);

            $service = new CommissionCalculationService();

            $monthly = $service->calculateForUser($user, 100.00, 'monthly');
            $biannual = $service->calculateForUser($user, 100.00, 'biannual');

            expect($monthly[0]['amount'])->toBe(30.00); // 30%
            expect($biannual[0]['amount'])->toBe(15.00); // 15%
        });
    });

    describe('calculateWithChain', function () {
        it('calculates with explicit chain and tiers', function () {
            $agent = Agent::factory()->create(['tier' => 'AGENT']);
            $mga = Agent::factory()->create(['tier' => 'MGA']);

            $service = new CommissionCalculationService();
            $commissions = $service->calculateWithChain(
                [$agent->id, $mga->id],
                [$agent->id => 'agent', $mga->id => 'mga'],
                100.00,
                'monthly'
            );

            expect($commissions)->toHaveCount(2);
            expect($commissions[0]['amount'])->toBe(30.00);
            expect($commissions[1]['amount'])->toBe(10.00);
        });
    });

    describe('getReferralData', function () {
        it('returns chain and tiers without calculating commissions', function () {
            $agent = Agent::factory()->create(['tier' => 'AGENT']);
            $user = User::factory()->create(['referring_agent_id' => $agent->id]);

            $service = new CommissionCalculationService();
            $data = $service->getReferralData($user);

            expect($data)->toHaveKeys(['chain', 'tiers']);
            expect($data['chain'])->toContain($agent->id);
            expect($data['tiers'][$agent->id])->toBe('agent');
        });
    });

    describe('dependency injection', function () {
        it('accepts custom chain builder and engine', function () {
            $chainBuilder = new ReferralChainBuilder();
            $engine = new CommissionCalculationEngine();

            $service = new CommissionCalculationService($chainBuilder, $engine);

            expect($service->getChainBuilder())->toBe($chainBuilder);
            expect($service->getEngine())->toBe($engine);
        });
    });
});

