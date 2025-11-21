<?php

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;

describe('Admin Subscription Configuration', function () {
    beforeEach(function () {
        // Create admin role if it doesn't exist
        if (!Role::where('name', 'admin')->exists()) {
            Role::create(['name' => 'admin', 'guard_name' => 'web']);
        }
    });

    describe('Authorization', function () {
        it('requires authentication', function () {
            $response = $this->get('/admin/subscription-configuration');
            $response->assertRedirect('/login');
        });

        it('requires admin role', function () {
            $user = User::factory()->create();
            $response = $this->actingAs($user)->get('/admin/subscription-configuration');
            $response->assertStatus(403);
        });

        it('allows admin users', function () {
            $user = User::factory()->create();
            $user->assignRole('admin');
            $response = $this->actingAs($user)->get('/admin/subscription-configuration');
            $response->assertStatus(200);
        });
    });

    describe('Get Configuration', function () {
        it('returns current configuration', function () {
            $user = User::factory()->create();
            $user->assignRole('admin');
            $response = $this->actingAs($user)->get('/admin/subscription-configuration');

            $response->assertStatus(200);
            $response->assertJsonStructure([
                'renewal' => ['idempotency_ttl_days', 'max_attempts', 'retry_schedule'],
                'rate_limiting' => ['hourly_limit', 'daily_limit'],
                'failure_alerts' => ['enabled', 'email_recipients', 'slack_webhook', 'pagerduty_key'],
            ]);
        });
    });

    describe('Update Retry Configuration', function () {
        it('validates required fields', function () {
            $user = User::factory()->create();
            $user->assignRole('admin');
            $response = $this->actingAs($user)->postJson('/admin/subscription-configuration/retry', []);

            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['idempotency_ttl_days', 'max_attempts', 'retry_schedule']);
        });

        it('validates retry schedule is in ascending order', function () {
            $user = User::factory()->create();
            $user->assignRole('admin');
            $response = $this->actingAs($user)->postJson('/admin/subscription-configuration/retry', [
                'idempotency_ttl_days' => 30,
                'max_attempts' => 5,
                'retry_schedule' => [5, 3, 1], // Not ascending
            ]);

            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['retry_schedule']);
        });

        it('validates schedule has enough entries', function () {
            $user = User::factory()->create();
            $user->assignRole('admin');
            $response = $this->actingAs($user)->postJson('/admin/subscription-configuration/retry', [
                'idempotency_ttl_days' => 30,
                'max_attempts' => 5,
                'retry_schedule' => [1, 3], // Only 2 entries, need 4
            ]);

            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['retry_schedule']);
        });

        it('updates retry configuration successfully', function () {
            $user = User::factory()->create();
            $user->assignRole('admin');
            $response = $this->actingAs($user)->postJson('/admin/subscription-configuration/retry', [
                'idempotency_ttl_days' => 45,
                'max_attempts' => 6,
                'retry_schedule' => [1, 2, 5, 10, 20, 30],
            ]);

            $response->assertStatus(200);
            $response->assertJsonPath('message', 'Retry configuration updated successfully');
            $response->assertJsonPath('configuration.idempotency_ttl_days', 45);
            $response->assertJsonPath('configuration.max_attempts', 6);
        });

        it('validates TTL range', function () {
            $user = User::factory()->create();
            $user->assignRole('admin');
            $response = $this->actingAs($user)->postJson('/admin/subscription-configuration/retry', [
                'idempotency_ttl_days' => 0,
                'max_attempts' => 5,
                'retry_schedule' => [1, 3, 7, 14, 30],
            ]);

            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['idempotency_ttl_days']);
        });

        it('validates max attempts range', function () {
            $user = User::factory()->create();
            $user->assignRole('admin');
            $response = $this->actingAs($user)->postJson('/admin/subscription-configuration/retry', [
                'idempotency_ttl_days' => 30,
                'max_attempts' => 11,
                'retry_schedule' => [1, 3, 7, 14, 30],
            ]);

            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['max_attempts']);
        });
    });

    describe('Update Rate Limit Configuration', function () {
        it('validates required fields', function () {
            $user = User::factory()->create();
            $user->assignRole('admin');
            $response = $this->actingAs($user)->postJson('/admin/subscription-configuration/rate-limits', []);

            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['hourly_limit', 'daily_limit']);
        });

        it('validates daily limit >= hourly limit', function () {
            $user = User::factory()->create();
            $user->assignRole('admin');
            $response = $this->actingAs($user)->postJson('/admin/subscription-configuration/rate-limits', [
                'hourly_limit' => 10,
                'daily_limit' => 5,
            ]);

            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['daily_limit']);
        });

        it('updates rate limit configuration successfully', function () {
            $user = User::factory()->create();
            $user->assignRole('admin');
            $response = $this->actingAs($user)->postJson('/admin/subscription-configuration/rate-limits', [
                'hourly_limit' => 10,
                'daily_limit' => 50,
            ]);

            $response->assertStatus(200);
            $response->assertJsonPath('message', 'Rate limit configuration updated successfully');
            $response->assertJsonPath('configuration.hourly_limit', 10);
            $response->assertJsonPath('configuration.daily_limit', 50);
        });

        it('validates limit ranges', function () {
            $user = User::factory()->create();
            $user->assignRole('admin');
            $response = $this->actingAs($user)->postJson('/admin/subscription-configuration/rate-limits', [
                'hourly_limit' => 0,
                'daily_limit' => 1001,
            ]);

            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['hourly_limit', 'daily_limit']);
        });
    });
});

