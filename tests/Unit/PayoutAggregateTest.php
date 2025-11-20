<?php

use App\Domain\Commission\PayoutAggregate;
use App\Domain\Commission\Events\PayoutProcessed;
use App\Domain\Commission\Events\PayoutRequested;

describe('PayoutAggregate', function () {
    describe('create', function () {
        it('creates a new payout aggregate with PayoutRequested event', function () {
            $uuid = 'payout-uuid-123';
            $payload = [
                'agent_id' => 'agent-123',
                'total_amount' => 150.00,
                'commission_ids' => ['comm-1', 'comm-2', 'comm-3'],
            ];

            $aggregate = PayoutAggregate::create($uuid, $payload);

            expect($aggregate->uuid)->toBe($uuid);
            expect($aggregate->agentId)->toBe('agent-123');
            expect($aggregate->totalAmount)->toBe(150.00);
            expect($aggregate->commissionIds)->toEqual(['comm-1', 'comm-2', 'comm-3']);
            expect($aggregate->status)->toBe('pending');
            $events = $aggregate->releaseEvents();
            expect($events)->toHaveCount(1);
            expect($events[0])->toBeInstanceOf(PayoutRequested::class);
        });
    });

    describe('process', function () {
        it('processes a pending payout', function () {
            $aggregate = PayoutAggregate::create('payout-uuid-123', [
                'agent_id' => 'agent-123',
                'total_amount' => 150.00,
                'commission_ids' => ['comm-1', 'comm-2'],
            ]);

            $aggregate->process(['processed_at' => now()->toIso8601String()]);

            expect($aggregate->status)->toBe('processed');
            $events = $aggregate->releaseEvents();
            expect($events)->toHaveCount(2);
            expect($events[1])->toBeInstanceOf(PayoutProcessed::class);
        });

        it('does not process an already processed payout', function () {
            $aggregate = PayoutAggregate::create('payout-uuid-123', [
                'agent_id' => 'agent-123',
                'total_amount' => 150.00,
                'commission_ids' => ['comm-1'],
            ]);

            $aggregate->process();
            $aggregate->process(); // Try to process again

            $events = $aggregate->releaseEvents();
            expect($events)->toHaveCount(2); // Only one process event
        });
    });

    describe('getCommissionCount', function () {
        it('returns the number of commissions in payout', function () {
            $aggregate = PayoutAggregate::create('payout-uuid-123', [
                'agent_id' => 'agent-123',
                'total_amount' => 150.00,
                'commission_ids' => ['comm-1', 'comm-2', 'comm-3'],
            ]);

            expect($aggregate->getCommissionCount())->toBe(3);
        });
    });

    describe('isReadyToProcess', function () {
        it('returns true when payout is ready to process', function () {
            $aggregate = PayoutAggregate::create('payout-uuid-123', [
                'agent_id' => 'agent-123',
                'total_amount' => 150.00,
                'commission_ids' => ['comm-1'],
            ]);

            expect($aggregate->isReadyToProcess())->toBeTrue();
        });

        it('returns false when payout has zero amount', function () {
            $aggregate = PayoutAggregate::create('payout-uuid-123', [
                'agent_id' => 'agent-123',
                'total_amount' => 0,
                'commission_ids' => [],
            ]);

            expect($aggregate->isReadyToProcess())->toBeFalse();
        });

        it('returns false when payout is already processed', function () {
            $aggregate = PayoutAggregate::create('payout-uuid-123', [
                'agent_id' => 'agent-123',
                'total_amount' => 150.00,
                'commission_ids' => ['comm-1'],
            ]);

            $aggregate->process();

            expect($aggregate->isReadyToProcess())->toBeFalse();
        });
    });
});

