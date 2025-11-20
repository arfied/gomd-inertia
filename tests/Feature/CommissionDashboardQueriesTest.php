<?php

use App\Application\Commission\Queries\GetAgentEarningsOverview;
use App\Application\Commission\Queries\GetAgentEarningsOverviewHandler;
use App\Application\Commission\Queries\GetRecentCommissions;
use App\Application\Commission\Queries\GetRecentCommissionsHandler;
use App\Application\Commission\Queries\GetAgentReferralHierarchy;
use App\Application\Commission\Queries\GetAgentReferralHierarchyHandler;
use App\Models\Agent;
use App\Models\AgentCommission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Commission Dashboard Queries', function () {
    describe('GetAgentEarningsOverview', function () {
        it('returns current and previous period earnings', function () {
            $user = User::factory()->create();
            $agent = Agent::factory()->create(['user_id' => $user->id]);

            // Create commissions for current month
            AgentCommission::factory()->create([
                'agent_id' => $agent->id,
                'commission_amount' => 100.00,
                'status' => 'pending',
                'created_at' => now(),
            ]);

            AgentCommission::factory()->create([
                'agent_id' => $agent->id,
                'commission_amount' => 50.00,
                'status' => 'pending',
                'created_at' => now(),
            ]);

            $handler = new GetAgentEarningsOverviewHandler();
            $result = $handler->handle(new GetAgentEarningsOverview($agent->id, 'month'));

            expect($result['current'])->toBe(150.00);
            expect($result['period'])->toBe('month');
        });

        it('excludes cancelled commissions', function () {
            $user = User::factory()->create();
            $agent = Agent::factory()->create(['user_id' => $user->id]);

            AgentCommission::factory()->create([
                'agent_id' => $agent->id,
                'commission_amount' => 100.00,
                'status' => 'pending',
            ]);

            AgentCommission::factory()->create([
                'agent_id' => $agent->id,
                'commission_amount' => 50.00,
                'status' => 'cancelled',
            ]);

            $handler = new GetAgentEarningsOverviewHandler();
            $result = $handler->handle(new GetAgentEarningsOverview($agent->id, 'month'));

            expect($result['current'])->toBe(100.00);
        });
    });

    describe('GetRecentCommissions', function () {
        it('returns paginated recent commissions', function () {
            $user = User::factory()->create();
            $agent = Agent::factory()->create(['user_id' => $user->id]);

            AgentCommission::factory(15)->create([
                'agent_id' => $agent->id,
                'status' => 'pending',
            ]);

            $handler = new GetRecentCommissionsHandler();
            $result = $handler->handle(new GetRecentCommissions($agent->id, 10, 1));

            expect($result['data'])->toHaveCount(10);
            expect($result['total'])->toBe(15);
            expect($result['per_page'])->toBe(10);
            expect($result['last_page'])->toBe(2);
        });

        it('excludes cancelled commissions', function () {
            $user = User::factory()->create();
            $agent = Agent::factory()->create(['user_id' => $user->id]);

            AgentCommission::factory(5)->create([
                'agent_id' => $agent->id,
                'status' => 'pending',
            ]);

            AgentCommission::factory(3)->create([
                'agent_id' => $agent->id,
                'status' => 'cancelled',
            ]);

            $handler = new GetRecentCommissionsHandler();
            $result = $handler->handle(new GetRecentCommissions($agent->id, 10, 1));

            expect($result['total'])->toBe(5);
        });
    });

    describe('GetAgentReferralHierarchy', function () {
        it('builds upline hierarchy', function () {
            $topAgent = Agent::factory()->create();
            $middleAgent = Agent::factory()->create(['referring_agent_id' => $topAgent->id]);
            $bottomAgent = Agent::factory()->create(['referring_agent_id' => $middleAgent->id]);

            $handler = new GetAgentReferralHierarchyHandler();
            $result = $handler->handle(new GetAgentReferralHierarchy($bottomAgent->id, 3));

            expect($result['upline'])->not->toBeEmpty();
            expect($result['upline']['id'])->toBe($middleAgent->id);
        });

        it('builds downline hierarchy', function () {
            $topAgent = Agent::factory()->create();
            $referral1 = Agent::factory()->create(['referring_agent_id' => $topAgent->id]);
            $referral2 = Agent::factory()->create(['referring_agent_id' => $topAgent->id]);

            $handler = new GetAgentReferralHierarchyHandler();
            $result = $handler->handle(new GetAgentReferralHierarchy($topAgent->id, 3));

            expect($result['downline'])->toHaveCount(2);
        });
    });
});

