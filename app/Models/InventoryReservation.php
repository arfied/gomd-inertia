<?php

namespace App\Models;

use App\Enums\InventoryReservationStatus;
use Illuminate\Database\Eloquent\Model;

/**
 * InventoryReservation Model
 *
 * Tracks inventory reservations for orders.
 * Used by the order fulfillment saga to reserve medications.
 */
class InventoryReservation extends Model
{
    protected $fillable = [
        'reservation_id',
        'warehouse_id',
        'status',
        'medications',
        'reserved_at',
        'released_at',
    ];

    protected $casts = [
        'medications' => 'array',
        'status' => InventoryReservationStatus::class,
        'reserved_at' => 'datetime',
        'released_at' => 'datetime',
    ];

    /**
     * Scope to get active reservations.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'reserved');
    }

    /**
     * Scope to get released reservations.
     */
    public function scopeReleased($query)
    {
        return $query->where('status', 'released');
    }
}

