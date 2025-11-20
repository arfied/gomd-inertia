<?php

use App\Application\Commission\ReferralChainBuilder;
use App\Models\Agent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

describe('ReferralChainBuilder', function () {
    uses(RefreshDatabase::class);
    describe('buildChainForUser', function () {
        it('builds chain for user with direct agent referrer', function () {
            $agent = Agent::factory()->create(['tier' => 'AGENT']);
            $user = User::factory()->create(['referring_agent_id' => $agent->id]);

            $builder = new ReferralChainBuilder();
            $chain = $builder->buildChainForUser($user);

            expect($chain)->toContain($agent->id);
        });

        it('builds chain through multiple agent levels', function () {
            $sfmo = Agent::factory()->create(['tier' => 'SFMO']);
            $fmo = Agent::factory()->create(['tier' => 'FMO', 'referring_agent_id' => $sfmo->id]);
            $agent = Agent::factory()->create(['tier' => 'AGENT', 'referring_agent_id' => $fmo->id]);
            $user = User::factory()->create(['referring_agent_id' => $agent->id]);

            $builder = new ReferralChainBuilder();
            $chain = $builder->buildChainForUser($user);

            expect($chain)->toEqual([$agent->id, $fmo->id, $sfmo->id]);
        });

        it('skips LOA and uses their agent referrer', function () {
            $agent = Agent::factory()->create(['tier' => 'AGENT']);
            $loa = User::factory()->create();
            $user = User::factory()->create([
                'referring_loa_id' => $loa->id,
                'referring_agent_id' => null,
            ]);

            // Set LOA's referring agent (LOA users have referring_agent_id)
            $loa->update(['referring_agent_id' => $agent->id]);

            $builder = new ReferralChainBuilder();
            $chain = $builder->buildChainForUser($user);

            expect($chain)->toContain($agent->id);
            expect($chain)->not->toContain($loa->id);
        });

        it('returns empty chain for user with no referrer', function () {
            $user = User::factory()->create([
                'referring_agent_id' => null,
                'referring_loa_id' => null,
            ]);

            $builder = new ReferralChainBuilder();
            $chain = $builder->buildChainForUser($user);

            expect($chain)->toBeEmpty();
        });

        it('prevents infinite loops in circular references', function () {
            $agent1 = Agent::factory()->create(['tier' => 'AGENT']);
            $agent2 = Agent::factory()->create(['tier' => 'MGA', 'referring_agent_id' => $agent1->id]);

            // Create circular reference (shouldn't happen in practice)
            $agent1->update(['referring_agent_id' => $agent2->id]);

            $user = User::factory()->create(['referring_agent_id' => $agent1->id]);

            $builder = new ReferralChainBuilder();
            $chain = $builder->buildChainForUser($user);

            // Should have both agents but not infinite loop
            expect($chain)->toHaveCount(2);
            expect($chain)->toContain($agent1->id, $agent2->id);
        });
    });

    describe('getAgentTiers', function () {
        it('returns tier map for agent IDs', function () {
            $agent1 = Agent::factory()->create(['tier' => 'AGENT']);
            $agent2 = Agent::factory()->create(['tier' => 'MGA']);

            $builder = new ReferralChainBuilder();
            $tiers = $builder->getAgentTiers([$agent1->id, $agent2->id]);

            expect($tiers)->toEqual([
                $agent1->id => 'agent',
                $agent2->id => 'mga',
            ]);
        });

        it('returns empty map for empty agent IDs', function () {
            $builder = new ReferralChainBuilder();
            $tiers = $builder->getAgentTiers([]);

            expect($tiers)->toBeEmpty();
        });
    });

    describe('buildCompleteReferralData', function () {
        it('returns chain and tiers together', function () {
            $agent = Agent::factory()->create(['tier' => 'AGENT']);
            $user = User::factory()->create(['referring_agent_id' => $agent->id]);

            $builder = new ReferralChainBuilder();
            $data = $builder->buildCompleteReferralData($user);

            expect($data)->toHaveKeys(['chain', 'tiers']);
            expect($data['chain'])->toContain($agent->id);
            expect($data['tiers'][$agent->id])->toBe('agent');
        });
    });
});

