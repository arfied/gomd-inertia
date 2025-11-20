<?php

namespace Tests\Unit\Domain\Shared;

use App\Domain\Shared\EventVersionMigrator;
use PHPUnit\Framework\TestCase;

class EventVersionMigratorTest extends TestCase
{
    protected function tearDown(): void
    {
        EventVersionMigrator::clear();
        parent::tearDown();
    }

    public function test_migrate_same_version_returns_unchanged(): void
    {
        $payload = ['name' => 'John', 'email' => 'john@example.com'];
        $result = EventVersionMigrator::migrate('user.created', 1, 1, $payload);
        $this->assertEquals($payload, $result);
    }

    public function test_register_and_migrate_single_version(): void
    {
        // Register migration from v1 to v2: add 'full_name' field
        EventVersionMigrator::register('user.created', 1, function ($payload) {
            return [
                ...$payload,
                'full_name' => $payload['name'] ?? '',
            ];
        });

        $oldPayload = ['name' => 'John', 'email' => 'john@example.com'];
        $result = EventVersionMigrator::migrate('user.created', 1, 2, $oldPayload);

        $this->assertEquals('John', $result['full_name']);
        $this->assertEquals('john@example.com', $result['email']);
    }

    public function test_migrate_multiple_versions(): void
    {
        // v1 -> v2: add 'full_name'
        EventVersionMigrator::register('user.created', 1, function ($payload) {
            return [
                ...$payload,
                'full_name' => $payload['name'] ?? '',
            ];
        });

        // v2 -> v3: add 'status'
        EventVersionMigrator::register('user.created', 2, function ($payload) {
            return [
                ...$payload,
                'status' => 'active',
            ];
        });

        $oldPayload = ['name' => 'John', 'email' => 'john@example.com'];
        $result = EventVersionMigrator::migrate('user.created', 1, 3, $oldPayload);

        $this->assertEquals('John', $result['full_name']);
        $this->assertEquals('john@example.com', $result['email']);
        $this->assertEquals('active', $result['status']);
    }

    public function test_migrate_missing_migration_throws(): void
    {
        // Register v1->v2 but not v2->v3
        EventVersionMigrator::register('user.created', 1, function ($payload) {
            return $payload;
        });

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No migration found for user.created from version 2 to 3');

        EventVersionMigrator::migrate('user.created', 1, 3, []);
    }

    public function test_has_migration_path_true(): void
    {
        EventVersionMigrator::register('user.created', 1, function ($p) { return $p; });
        EventVersionMigrator::register('user.created', 2, function ($p) { return $p; });

        $this->assertTrue(EventVersionMigrator::hasMigrationPath('user.created', 1, 3));
    }

    public function test_has_migration_path_false(): void
    {
        EventVersionMigrator::register('user.created', 1, function ($p) { return $p; });

        $this->assertFalse(EventVersionMigrator::hasMigrationPath('user.created', 1, 3));
    }

    public function test_has_migration_path_same_version(): void
    {
        $this->assertTrue(EventVersionMigrator::hasMigrationPath('user.created', 1, 1));
    }

    public function test_clear_removes_all_migrations(): void
    {
        EventVersionMigrator::register('user.created', 1, function ($p) { return $p; });
        EventVersionMigrator::clear();

        $this->assertFalse(EventVersionMigrator::hasMigrationPath('user.created', 1, 2));
    }
}

