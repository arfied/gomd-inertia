<?php

namespace App\Enums;

/**
 * ShipmentStatus Enum
 *
 * Defines valid status values for shipments.
 */
enum ShipmentStatus: string
{
    case INITIATED = 'initiated';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';

    /**
     * Get the label for display.
     */
    public function label(): string
    {
        return match ($this) {
            self::INITIATED => 'Initiated',
            self::SHIPPED => 'Shipped',
            self::DELIVERED => 'Delivered',
            self::CANCELLED => 'Cancelled',
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

    /**
     * Check if shipment can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return $this !== self::SHIPPED && $this !== self::DELIVERED;
    }
}

