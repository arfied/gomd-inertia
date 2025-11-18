<?php

namespace App\Models;

use App\Enums\ShipmentStatus;
use Illuminate\Database\Eloquent\Model;

/**
 * Shipment Model
 *
 * Tracks shipments for orders.
 * Used by the order fulfillment saga to manage shipment lifecycle.
 */
class Shipment extends Model
{
    protected $fillable = [
        'shipment_id',
        'order_uuid',
        'shipping_address',
        'shipping_method',
        'tracking_number',
        'status',
        'initiated_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
    ];

    protected $casts = [
        'status' => ShipmentStatus::class,
        'initiated_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Scope to get initiated shipments.
     */
    public function scopeInitiated($query)
    {
        return $query->where('status', 'initiated');
    }

    /**
     * Scope to get shipped shipments.
     */
    public function scopeShipped($query)
    {
        return $query->where('status', 'shipped');
    }

    /**
     * Scope to get delivered shipments.
     */
    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    /**
     * Scope to get cancelled shipments.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Get shipments for a specific order.
     */
    public function scopeForOrder($query, string $orderUuid)
    {
        return $query->where('order_uuid', $orderUuid);
    }
}

