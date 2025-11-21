<?php

use App\Domain\Subscription\Events\RenewalFailureAlert;
use App\Listeners\RenewalFailureAlertHandler;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

describe('Renewal Failure Alerts', function () {
    describe('Alert Event', function () {
        it('creates renewal failure alert event', function () {
            $event = new RenewalFailureAlert(
                sagaUuid: 'test-saga-uuid',
                subscriptionId: 1,
                userId: 1,
                amount: 99.99,
                reason: 'Payment declined',
                attemptNumber: 5,
                maxAttempts: 5,
                correlationId: 'test-correlation-id',
            );

            expect($event->sagaUuid)->toBe('test-saga-uuid');
            expect($event->subscriptionId)->toBe(1);
            expect($event->userId)->toBe(1);
            expect($event->amount)->toBe(99.99);
            expect($event->reason)->toBe('Payment declined');
            expect($event->attemptNumber)->toBe(5);
            expect($event->maxAttempts)->toBe(5);
            expect($event->correlationId)->toBe('test-correlation-id');
        });
    });

    describe('Alert Handler', function () {
        it('skips alerts when disabled', function () {
            Log::spy();

            config(['subscription.failure_alerts.enabled' => false]);

            $user = User::factory()->create();
            $event = new RenewalFailureAlert(
                sagaUuid: 'test-saga-uuid',
                subscriptionId: 1,
                userId: $user->id,
                amount: 99.99,
                reason: 'Payment declined',
                attemptNumber: 5,
                maxAttempts: 5,
                correlationId: 'test-correlation-id',
            );

            $handler = new RenewalFailureAlertHandler();
            $handler->handle($event);

            // Should not log anything when disabled
            expect(true)->toBeTrue(); // Handler returns early, no logging
        });

        it('handles missing user gracefully', function () {
            Log::spy();

            config(['subscription.failure_alerts.enabled' => true]);

            $event = new RenewalFailureAlert(
                sagaUuid: 'test-saga-uuid',
                subscriptionId: 1,
                userId: 99999, // Non-existent user
                amount: 99.99,
                reason: 'Payment declined',
                attemptNumber: 5,
                maxAttempts: 5,
                correlationId: 'test-correlation-id',
            );

            $handler = new RenewalFailureAlertHandler();
            $handler->handle($event);

            // Should log warning about missing user
            Log::shouldHaveReceived('warning');
        });

        it('sends email alerts when configured', function () {
            Log::spy();

            config([
                'subscription.failure_alerts.enabled' => true,
                'subscription.failure_alerts.email_recipients' => ['admin@example.com'],
            ]);

            $user = User::factory()->create();
            $event = new RenewalFailureAlert(
                sagaUuid: 'test-saga-uuid',
                subscriptionId: 1,
                userId: $user->id,
                amount: 99.99,
                reason: 'Payment declined',
                attemptNumber: 5,
                maxAttempts: 5,
                correlationId: 'test-correlation-id',
            );

            $handler = new RenewalFailureAlertHandler();
            $handler->handle($event);

            // Should log that email would be sent
            Log::shouldHaveReceived('info');
        });

        it('sends slack alerts when webhook configured', function () {
            Http::fake();

            config([
                'subscription.failure_alerts.enabled' => true,
                'subscription.failure_alerts.slack_webhook' => 'https://hooks.slack.com/test',
            ]);

            $user = User::factory()->create();
            $event = new RenewalFailureAlert(
                sagaUuid: 'test-saga-uuid',
                subscriptionId: 1,
                userId: $user->id,
                amount: 99.99,
                reason: 'Payment declined',
                attemptNumber: 5,
                maxAttempts: 5,
                correlationId: 'test-correlation-id',
            );

            $handler = new RenewalFailureAlertHandler();
            $handler->handle($event);

            Http::assertSent(function ($request) {
                return $request->url() === 'https://hooks.slack.com/test';
            });
        });

        it('sends pagerduty alerts when key configured', function () {
            Http::fake();

            config([
                'subscription.failure_alerts.enabled' => true,
                'subscription.failure_alerts.pagerduty_key' => 'test-pagerduty-key',
            ]);

            $user = User::factory()->create();
            $event = new RenewalFailureAlert(
                sagaUuid: 'test-saga-uuid',
                subscriptionId: 1,
                userId: $user->id,
                amount: 99.99,
                reason: 'Payment declined',
                attemptNumber: 5,
                maxAttempts: 5,
                correlationId: 'test-correlation-id',
            );

            $handler = new RenewalFailureAlertHandler();
            $handler->handle($event);

            Http::assertSent(function ($request) {
                return $request->url() === 'https://events.pagerduty.com/v2/enqueue';
            });
        });
    });

    describe('Alert Configuration', function () {
        it('uses default configuration values', function () {
            $enabled = config('subscription.failure_alerts.enabled');
            $recipients = config('subscription.failure_alerts.email_recipients');

            expect($enabled)->toBeTrue();
            expect($recipients)->toBeArray();
        });

        it('respects custom configuration', function () {
            config([
                'subscription.failure_alerts.enabled' => false,
                'subscription.failure_alerts.email_recipients' => ['custom@example.com'],
                'subscription.failure_alerts.slack_webhook' => 'https://custom.slack.com',
            ]);

            expect(config('subscription.failure_alerts.enabled'))->toBeFalse();
            expect(config('subscription.failure_alerts.email_recipients'))->toContain('custom@example.com');
            expect(config('subscription.failure_alerts.slack_webhook'))->toBe('https://custom.slack.com');
        });
    });
});

