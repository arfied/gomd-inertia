<?php

namespace App\Enums;

/**
 * InventoryReservationStatus Enum
 *
 * Defines valid status values for inventory reservations.
 */
enum InventoryReservationStatus: string
{
    case RESERVED = 'reserved';
    case RELEASED = 'released';

    /**
     * Get the label for display.
     */
    public function label(): string
    {
        return match ($this) {
            self::RESERVED => 'Reserved',
            self::RELEASED => 'Released',
        };
    }

    /**
     * Get all available statuses.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_map(fn ($case) => $case->value, self::cases());
    }
}

