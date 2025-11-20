<?php

use App\Domain\Commission\CommissionAggregate;
use App\Domain\Commission\Events\CommissionCancelled;
use App\Domain\Commission\Events\CommissionEarned;

describe('CommissionAggregate', function () {
    describe('create', function () {
        it('creates a new commission aggregate with CommissionEarned event', function () {
            $uuid = 'commission-uuid-123';
            $payload = [
                'order_id' => 'order-123',
                'patient_id' => 'patient-123',
                'recipient_id' => 'agent-123',
                'recipient_type' => 'agent',
                'amount' => 30.00,
                'rate' => 30.0,
                'order_total' => 100.00,
                'product_type' => 'subscription',
                'commission_frequency' => 'monthly',
                'referral_chain' => ['agent-123', 'mga-123'],
            ];

            $aggregate = CommissionAggregate::create($uuid, $payload);

            expect($aggregate->uuid)->toBe($uuid);
            expect($aggregate->orderId)->toBe('order-123');
            expect($aggregate->recipientId)->toBe('agent-123');
            expect($aggregate->amount)->toBe(30.00);
            expect($aggregate->status)->toBe('pending');
            $events = $aggregate->releaseEvents();
            expect($events)->toHaveCount(1);
            expect($events[0])->toBeInstanceOf(CommissionEarned::class);
        });
    });

    describe('cancel', function () {
        it('cancels a pending commission', function () {
            $aggregate = CommissionAggregate::create('commission-uuid-123', [
                'order_id' => 'order-123',
                'recipient_id' => 'agent-123',
                'recipient_type' => 'agent',
                'amount' => 30.00,
                'rate' => 30.0,
            ]);

            $aggregate->cancel(['reason' => 'Order cancelled']);

            expect($aggregate->status)->toBe('cancelled');
            $events = $aggregate->releaseEvents();
            expect($events)->toHaveCount(2);
            expect($events[1])->toBeInstanceOf(CommissionCancelled::class);
        });

        it('does not cancel an already cancelled commission', function () {
            $aggregate = CommissionAggregate::create('commission-uuid-123', [
                'order_id' => 'order-123',
                'recipient_id' => 'agent-123',
                'recipient_type' => 'agent',
                'amount' => 30.00,
                'rate' => 30.0,
            ]);

            $aggregate->cancel();
            $aggregate->cancel(); // Try to cancel again

            $events = $aggregate->releaseEvents();
            expect($events)->toHaveCount(2); // Only one cancel event
        });
    });

    describe('event application', function () {
        it('applies CommissionEarned event correctly', function () {
            $payload = [
                'order_id' => 'order-123',
                'patient_id' => 'patient-123',
                'recipient_id' => 'agent-123',
                'recipient_type' => 'agent',
                'amount' => 30.00,
                'rate' => 30.0,
                'order_total' => 100.00,
                'product_type' => 'subscription',
                'commission_frequency' => 'monthly',
                'referral_chain' => ['agent-123', 'mga-123'],
            ];

            $aggregate = CommissionAggregate::create('commission-uuid-123', $payload);

            expect($aggregate->orderId)->toBe('order-123');
            expect($aggregate->patientId)->toBe('patient-123');
            expect($aggregate->recipientType)->toBe('agent');
            expect($aggregate->commissionFrequency)->toBe('monthly');
            expect($aggregate->referralChain)->toEqual(['agent-123', 'mga-123']);
        });
    });
});

