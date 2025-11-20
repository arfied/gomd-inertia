<?php

namespace App\Domain\Shared;

/**
 * Handles event schema migration for different event versions.
 *
 * When event schemas change, this class helps migrate old event data
 * to the new format for proper deserialization.
 */
class EventVersionMigrator
{
    /** @var array<string, array<int, callable>> */
    private static array $migrations = [];

    /**
     * Register a migration for an event type.
     *
     * @param  string  $eventType The event type (e.g., 'subscription.created')
     * @param  int  $fromVersion The version to migrate from
     * @param  callable  $migrator Function that transforms old payload to new
     */
    public static function register(string $eventType, int $fromVersion, callable $migrator): void
    {
        if (!isset(self::$migrations[$eventType])) {
            self::$migrations[$eventType] = [];
        }

        self::$migrations[$eventType][$fromVersion] = $migrator;
    }

    /**
     * Migrate event data from an old version to the current version.
     *
     * @param  string  $eventType The event type
     * @param  int  $fromVersion The version of the stored event
     * @param  int  $toVersion The target version
     * @param  array<string, mixed>  $payload The event payload
     *
     * @return array<string, mixed> The migrated payload
     */
    public static function migrate(
        string $eventType,
        int $fromVersion,
        int $toVersion,
        array $payload
    ): array {
        if ($fromVersion === $toVersion) {
            return $payload;
        }

        $current = $payload;

        // Apply migrations sequentially from fromVersion to toVersion
        for ($version = $fromVersion; $version < $toVersion; $version++) {
            if (!isset(self::$migrations[$eventType][$version])) {
                throw new \RuntimeException(
                    "No migration found for {$eventType} from version {$version} to " . ($version + 1)
                );
            }

            $migrator = self::$migrations[$eventType][$version];
            $current = $migrator($current);
        }

        return $current;
    }

    /**
     * Check if a migration path exists.
     *
     * @param  string  $eventType The event type
     * @param  int  $fromVersion The version to migrate from
     * @param  int  $toVersion The target version
     */
    public static function hasMigrationPath(string $eventType, int $fromVersion, int $toVersion): bool
    {
        if ($fromVersion === $toVersion) {
            return true;
        }

        for ($version = $fromVersion; $version < $toVersion; $version++) {
            if (!isset(self::$migrations[$eventType][$version])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Clear all registered migrations (useful for testing).
     */
    public static function clear(): void
    {
        self::$migrations = [];
    }
}

