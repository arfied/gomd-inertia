<?php

use App\Domain\Commission\ReferralHierarchyAggregate;
use App\Domain\Commission\Events\ReferralHierarchyCreated;

describe('ReferralHierarchyAggregate', function () {
    describe('create', function () {
        it('creates a new referral hierarchy aggregate', function () {
            $uuid = 'hierarchy-uuid-123';
            $payload = [
                'agent_id' => 'agent-123',
                'parent_agent_id' => 'mga-123',
                'tier' => 'agent',
                'commission_rates' => [
                    'monthly' => 30.0,
                    'biannual' => 15.0,
                    'annual' => 15.0,
                ],
                'downline_agents' => ['loa-1', 'loa-2'],
                'status' => 'active',
            ];

            $aggregate = ReferralHierarchyAggregate::create($uuid, $payload);
            info($aggregate);

            expect($aggregate->uuid)->toBe($uuid);
            expect($aggregate->agentId)->toBe('agent-123');
            expect($aggregate->parentAgentId)->toBe('mga-123');
            expect($aggregate->tier)->toBe('agent');
            expect($aggregate->status)->toBe('active');
            $events = $aggregate->releaseEvents();
            expect($events)->toHaveCount(1);
            expect($events[0])->toBeInstanceOf(ReferralHierarchyCreated::class);
        });
    });

    describe('getCommissionRate', function () {
        it('returns commission rate for specific frequency', function () {
            $aggregate = ReferralHierarchyAggregate::create('hierarchy-uuid-123', [
                'agent_id' => 'agent-123',
                'tier' => 'agent',
                'commission_rates' => [
                    'monthly' => 30.0,
                    'biannual' => 15.0,
                    'annual' => 15.0,
                ],
            ]);

            expect($aggregate->getCommissionRate('monthly'))->toBe(30.0);
            expect($aggregate->getCommissionRate('biannual'))->toBe(15.0);
            expect($aggregate->getCommissionRate('annual'))->toBe(15.0);
        });

        it('returns zero for missing frequency', function () {
            $aggregate = ReferralHierarchyAggregate::create('hierarchy-uuid-123', [
                'agent_id' => 'agent-123',
                'tier' => 'agent',
                'commission_rates' => ['monthly' => 30.0],
            ]);

            expect($aggregate->getCommissionRate('invalid'))->toBe(0.0);
        });
    });

    describe('hasParent', function () {
        it('returns true when agent has parent', function () {
            $aggregate = ReferralHierarchyAggregate::create('hierarchy-uuid-123', [
                'agent_id' => 'agent-123',
                'parent_agent_id' => 'mga-123',
                'tier' => 'agent',
            ]);

            expect($aggregate->hasParent())->toBeTrue();
        });

        it('returns false when agent has no parent', function () {
            $aggregate = ReferralHierarchyAggregate::create('hierarchy-uuid-123', [
                'agent_id' => 'sfmo-123',
                'parent_agent_id' => null,
                'tier' => 'sfmo',
            ]);

            expect($aggregate->hasParent())->toBeFalse();
        });
    });

    describe('getReferralChain', function () {
        it('returns referral chain including agent and downline', function () {
            $aggregate = ReferralHierarchyAggregate::create('hierarchy-uuid-123', [
                'agent_id' => 'agent-123',
                'downline_agents' => ['loa-1', 'loa-2'],
                'tier' => 'agent',
            ]);

            $chain = $aggregate->getReferralChain();

            expect($chain)->toEqual(['agent-123', 'loa-1', 'loa-2']);
        });
    });
});

