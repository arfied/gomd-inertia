<?php

namespace App\Domain\Shared;

use InvalidArgumentException;

/**
 * Validates UUID format and values.
 */
class UuidValidator
{
    /**
     * UUID v4 regex pattern.
     */
    private const UUID_V4_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

    /**
     * Generic UUID pattern (v1-v5).
     */
    private const UUID_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

    /**
     * Validate that a string is a valid UUID (v1-v5).
     *
     * @param  string  $uuid
     *
     * @throws InvalidArgumentException If UUID is invalid
     */
    public static function validate(string $uuid): void
    {
        if (empty($uuid)) {
            throw new InvalidArgumentException('UUID cannot be empty');
        }

        if (!preg_match(self::UUID_PATTERN, $uuid)) {
            throw new InvalidArgumentException(
                "Invalid UUID format: {$uuid}"
            );
        }
    }

    /**
     * Validate that a string is a valid UUID v4.
     *
     * @param  string  $uuid
     *
     * @throws InvalidArgumentException If UUID is not v4
     */
    public static function validateV4(string $uuid): void
    {
        self::validate($uuid);

        if (!preg_match(self::UUID_V4_PATTERN, $uuid)) {
            throw new InvalidArgumentException(
                "UUID must be version 4: {$uuid}"
            );
        }
    }

    /**
     * Check if a string is a valid UUID without throwing.
     *
     * @param  string  $uuid
     */
    public static function isValid(string $uuid): bool
    {
        try {
            self::validate($uuid);
            return true;
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    /**
     * Check if a string is a valid UUID v4 without throwing.
     *
     * @param  string  $uuid
     */
    public static function isValidV4(string $uuid): bool
    {
        try {
            self::validateV4($uuid);
            return true;
        } catch (InvalidArgumentException) {
            return false;
        }
    }
}

