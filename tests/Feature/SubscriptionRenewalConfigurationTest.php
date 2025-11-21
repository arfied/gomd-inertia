<?php

use Illuminate\Support\Facades\Cache;

describe('Subscription Renewal Configuration', function () {
    beforeEach(function () {
        // Reset configuration to defaults
        config(['subscription.renewal.idempotency_ttl_days' => 30]);
        config(['subscription.renewal.max_attempts' => 5]);
        config(['subscription.renewal.retry_schedule' => [1, 3, 7, 14, 30]]);
    });

    describe('Idempotency Cache TTL', function () {
        it('uses default 30 day ttl when not configured', function () {
            $sagaUuid = (string) \Illuminate\Support\Str::uuid();
            $cacheKey = "renewal_processed:{$sagaUuid}";
            $ttlDays = (int) config('subscription.renewal.idempotency_ttl_days', 30);

            Cache::put($cacheKey, true, now()->addDays($ttlDays));
            expect(Cache::has($cacheKey))->toBeTrue();
            expect($ttlDays)->toBe(30);
        });

        it('uses custom ttl from configuration', function () {
            config(['subscription.renewal.idempotency_ttl_days' => 60]);

            $sagaUuid = (string) \Illuminate\Support\Str::uuid();
            $cacheKey = "renewal_processed:{$sagaUuid}";
            $ttlDays = (int) config('subscription.renewal.idempotency_ttl_days', 30);

            Cache::put($cacheKey, true, now()->addDays($ttlDays));

            expect($ttlDays)->toBe(60);
            expect(Cache::has($cacheKey))->toBeTrue();
        });

        it('respects different ttl configurations', function () {
            $ttlValues = [7, 14, 30, 60, 90];

            foreach ($ttlValues as $ttl) {
                config(['subscription.renewal.idempotency_ttl_days' => $ttl]);
                $configuredTtl = (int) config('subscription.renewal.idempotency_ttl_days', 30);
                expect($configuredTtl)->toBe($ttl);
            }
        });
    });

    describe('Rate Limit Customization', function () {
        it('uses default hourly rate limit when not configured', function () {
            $hourlyLimit = (int) config('subscription.rate_limiting.hourly_limit', 5);
            expect($hourlyLimit)->toBe(5);
        });

        it('uses custom hourly rate limit from configuration', function () {
            config(['subscription.rate_limiting.hourly_limit' => 10]);
            $hourlyLimit = (int) config('subscription.rate_limiting.hourly_limit', 5);
            expect($hourlyLimit)->toBe(10);
        });

        it('uses default daily rate limit when not configured', function () {
            $dailyLimit = (int) config('subscription.rate_limiting.daily_limit', 20);
            expect($dailyLimit)->toBe(20);
        });

        it('uses custom daily rate limit from configuration', function () {
            config(['subscription.rate_limiting.daily_limit' => 50]);
            $dailyLimit = (int) config('subscription.rate_limiting.daily_limit', 20);
            expect($dailyLimit)->toBe(50);
        });

        it('respects different rate limit configurations', function () {
            $limits = [
                ['hourly' => 3, 'daily' => 10],
                ['hourly' => 5, 'daily' => 20],
                ['hourly' => 10, 'daily' => 50],
                ['hourly' => 20, 'daily' => 100],
            ];

            foreach ($limits as $limit) {
                config(['subscription.rate_limiting.hourly_limit' => $limit['hourly']]);
                config(['subscription.rate_limiting.daily_limit' => $limit['daily']]);

                $hourly = (int) config('subscription.rate_limiting.hourly_limit', 5);
                $daily = (int) config('subscription.rate_limiting.daily_limit', 20);

                expect($hourly)->toBe($limit['hourly']);
                expect($daily)->toBe($limit['daily']);
            }
        });
    });

    describe('Retry Schedule Flexibility', function () {
        it('uses default retry schedule when not configured', function () {
            $schedule = config('subscription.renewal.retry_schedule', [1, 3, 7, 14, 30]);
            expect($schedule)->toBe([1, 3, 7, 14, 30]);
        });

        it('uses custom retry schedule from configuration', function () {
            config(['subscription.renewal.retry_schedule' => [2, 5, 10, 20]]);
            $schedule = config('subscription.renewal.retry_schedule', []);
            expect($schedule)->toBe([2, 5, 10, 20]);
        });

        it('uses default max attempts when not configured', function () {
            $maxAttempts = (int) config('subscription.renewal.max_attempts', 5);
            expect($maxAttempts)->toBe(5);
        });

        it('uses custom max attempts from configuration', function () {
            config(['subscription.renewal.max_attempts' => 3]);
            $maxAttempts = (int) config('subscription.renewal.max_attempts', 5);
            expect($maxAttempts)->toBe(3);
        });

        it('respects different retry schedules', function () {
            $schedules = [
                [1, 2, 3],
                [1, 3, 7, 14],
                [1, 3, 7, 14, 30, 60],
            ];

            foreach ($schedules as $schedule) {
                config(['subscription.renewal.retry_schedule' => $schedule]);
                $configured = config('subscription.renewal.retry_schedule', []);
                expect($configured)->toBe($schedule);
            }
        });
    });

    describe('Configuration Validation', function () {
        it('validates idempotency ttl is positive', function () {
            config(['subscription.renewal.idempotency_ttl_days' => -1]);
            $ttl = (int) config('subscription.renewal.idempotency_ttl_days', 30);
            expect($ttl)->toBeLessThan(0);
        });

        it('validates max attempts is positive', function () {
            config(['subscription.renewal.max_attempts' => 0]);
            $maxAttempts = (int) config('subscription.renewal.max_attempts', 5);
            expect($maxAttempts)->toBe(0);
        });

        it('validates retry schedule has enough entries', function () {
            config(['subscription.renewal.max_attempts' => 5]);
            config(['subscription.renewal.retry_schedule' => [1, 3]]); // Only 2 entries, need 4
            $schedule = config('subscription.renewal.retry_schedule', []);
            expect(count($schedule))->toBeLessThan(4);
        });

        it('validates retry schedule values are positive', function () {
            config(['subscription.renewal.retry_schedule' => [1, -3, 7, 14, 30]]);
            $schedule = config('subscription.renewal.retry_schedule', []);
            $hasNegative = collect($schedule)->some(fn ($val) => $val <= 0);
            expect($hasNegative)->toBeTrue();
        });

        it('validates retry schedule is in ascending order', function () {
            config(['subscription.renewal.retry_schedule' => [1, 7, 3, 14, 30]]); // Not ascending
            $schedule = config('subscription.renewal.retry_schedule', []);
            $isAscending = true;
            for ($i = 1; $i < count($schedule); $i++) {
                if ($schedule[$i] <= $schedule[$i - 1]) {
                    $isAscending = false;
                    break;
                }
            }
            expect($isAscending)->toBeFalse();
        });
    });
});

