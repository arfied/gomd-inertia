<?php

use App\Models\PaymentMethod;
use App\Models\User;

describe('Payment Method Verification Status Validation', function () {
    describe('ACH Verification', function () {
        it('allows verified ACH payment methods', function () {
            $user = User::factory()->create();
            $paymentMethod = PaymentMethod::factory()
                ->ach()
                ->create([
                    'user_id' => $user->id,
                    'verification_status' => 'verified',
                ]);

            expect($paymentMethod->isValid())->toBeTrue();
            expect($paymentMethod->getValidationError())->toBeNull();
        });

        it('rejects pending ACH payment methods', function () {
            $user = User::factory()->create();
            $paymentMethod = PaymentMethod::factory()
                ->ach()
                ->create([
                    'user_id' => $user->id,
                    'verification_status' => 'pending',
                ]);

            expect($paymentMethod->isValid())->toBeFalse();
            expect($paymentMethod->getValidationError())->toContain('pending');
        });

        it('rejects failed ACH payment methods', function () {
            $user = User::factory()->create();
            $paymentMethod = PaymentMethod::factory()
                ->ach()
                ->create([
                    'user_id' => $user->id,
                    'verification_status' => 'failed',
                ]);

            expect($paymentMethod->isValid())->toBeFalse();
            expect($paymentMethod->getValidationError())->toContain('failed');
        });

        it('rejects unverified ACH payment methods', function () {
            $user = User::factory()->create();
            $paymentMethod = PaymentMethod::factory()
                ->ach()
                ->create([
                    'user_id' => $user->id,
                    'verification_status' => 'active', // Invalid status
                ]);

            expect($paymentMethod->isValid())->toBeFalse();
            expect($paymentMethod->getValidationError())->toContain('not verified');
        });

        it('allows credit cards regardless of verification status', function () {
            $user = User::factory()->create();
            $paymentMethod = PaymentMethod::factory()
                ->creditCard()
                ->create([
                    'user_id' => $user->id,
                    'verification_status' => 'pending', // Credit cards don't need verification
                ]);

            expect($paymentMethod->isValid())->toBeTrue();
            expect($paymentMethod->getValidationError())->toBeNull();
        });

        it('allows invoices regardless of verification status', function () {
            $user = User::factory()->create();
            $paymentMethod = PaymentMethod::factory()
                ->invoice()
                ->create([
                    'user_id' => $user->id,
                    'verification_status' => 'pending', // Invoices don't need verification
                ]);

            expect($paymentMethod->isValid())->toBeTrue();
            expect($paymentMethod->getValidationError())->toBeNull();
        });

        it('tracks verification attempts', function () {
            $user = User::factory()->create();
            $paymentMethod = PaymentMethod::factory()
                ->ach()
                ->create([
                    'user_id' => $user->id,
                    'verification_status' => 'pending',
                    'verification_attempts' => 0,
                ]);

            expect($paymentMethod->verification_attempts)->toBe(0);

            $paymentMethod->markVerificationFailed();
            expect($paymentMethod->fresh()->verification_attempts)->toBe(1);

            $paymentMethod->markVerificationFailed();
            expect($paymentMethod->fresh()->verification_attempts)->toBe(2);
        });

        it('marks verification timestamp on status change', function () {
            $user = User::factory()->create();
            $paymentMethod = PaymentMethod::factory()
                ->ach()
                ->create([
                    'user_id' => $user->id,
                    'verification_status' => 'pending',
                    'last_verification_attempt_at' => null,
                ]);

            expect($paymentMethod->last_verification_attempt_at)->toBeNull();

            $paymentMethod->markAsVerified();
            expect($paymentMethod->fresh()->last_verification_attempt_at)->not->toBeNull();
        });
    });

    describe('Verification Status Scopes', function () {
        it('filters verified payment methods', function () {
            $user = User::factory()->create();
            PaymentMethod::factory()->ach()->create(['user_id' => $user->id, 'verification_status' => 'verified']);
            PaymentMethod::factory()->ach()->create(['user_id' => $user->id, 'verification_status' => 'pending']);

            $verified = PaymentMethod::where('user_id', $user->id)->verified()->get();
            expect($verified)->toHaveCount(1);
            expect($verified->first()->verification_status)->toBe('verified');
        });

        it('filters pending verification payment methods', function () {
            $user = User::factory()->create();
            PaymentMethod::factory()->ach()->create(['user_id' => $user->id, 'verification_status' => 'verified']);
            PaymentMethod::factory()->ach()->create(['user_id' => $user->id, 'verification_status' => 'pending']);

            $pending = PaymentMethod::where('user_id', $user->id)->pendingVerification()->get();
            expect($pending)->toHaveCount(1);
            expect($pending->first()->verification_status)->toBe('pending');
        });
    });
});

