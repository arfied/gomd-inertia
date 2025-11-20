<?php

namespace App\Domain\Shared;

use InvalidArgumentException;

/**
 * Validates event payloads to ensure data integrity.
 */
class PayloadValidator
{
    /**
     * Validate that required fields are present in payload.
     *
     * @param  array<string, mixed>  $payload
     * @param  array<string>  $requiredFields
     *
     * @throws InvalidArgumentException If required fields are missing
     */
    public static function validateRequired(array $payload, array $requiredFields): void
    {
        $missing = [];
        foreach ($requiredFields as $field) {
            if (!isset($payload[$field]) || $payload[$field] === '') {
                $missing[] = $field;
            }
        }

        if (!empty($missing)) {
            throw new InvalidArgumentException(
                'Missing required fields: ' . implode(', ', $missing)
            );
        }
    }

    /**
     * Validate that a field is of a specific type.
     *
     * @param  array<string, mixed>  $payload
     * @param  string  $field
     * @param  string  $type
     *
     * @throws InvalidArgumentException If field type is invalid
     */
    public static function validateType(array $payload, string $field, string $type): void
    {
        if (!isset($payload[$field])) {
            return;
        }

        $value = $payload[$field];
        $actualType = gettype($value);

        if ($type === 'numeric' && !is_numeric($value)) {
            throw new InvalidArgumentException(
                "Field '{$field}' must be numeric, got {$actualType}"
            );
        }

        if ($type === 'array' && !is_array($value)) {
            throw new InvalidArgumentException(
                "Field '{$field}' must be an array, got {$actualType}"
            );
        }

        if ($type === 'string' && !is_string($value)) {
            throw new InvalidArgumentException(
                "Field '{$field}' must be a string, got {$actualType}"
            );
        }

        if ($type === 'int' && !is_int($value)) {
            throw new InvalidArgumentException(
                "Field '{$field}' must be an integer, got {$actualType}"
            );
        }

        if ($type === 'float' && !is_float($value) && !is_int($value)) {
            throw new InvalidArgumentException(
                "Field '{$field}' must be a float, got {$actualType}"
            );
        }
    }

    /**
     * Validate that a field value is within a range.
     *
     * @param  array<string, mixed>  $payload
     * @param  string  $field
     * @param  int|float  $min
     * @param  int|float  $max
     *
     * @throws InvalidArgumentException If value is out of range
     */
    public static function validateRange(array $payload, string $field, $min, $max): void
    {
        if (!isset($payload[$field])) {
            return;
        }

        $value = $payload[$field];

        if ($value < $min || $value > $max) {
            throw new InvalidArgumentException(
                "Field '{$field}' must be between {$min} and {$max}, got {$value}"
            );
        }
    }

    /**
     * Validate that a field value is one of allowed values.
     *
     * @param  array<string, mixed>  $payload
     * @param  string  $field
     * @param  array<string|int>  $allowedValues
     *
     * @throws InvalidArgumentException If value is not allowed
     */
    public static function validateEnum(array $payload, string $field, array $allowedValues): void
    {
        if (!isset($payload[$field])) {
            return;
        }

        $value = $payload[$field];

        if (!in_array($value, $allowedValues, true)) {
            $allowed = implode(', ', $allowedValues);
            throw new InvalidArgumentException(
                "Field '{$field}' must be one of: {$allowed}, got {$value}"
            );
        }
    }
}

