<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderFulfillmentSaga extends Model
{
    protected $table = 'order_fulfillment_sagas';

    protected $fillable = [
        'saga_uuid',
        'order_uuid',
        'state',
        'compensation_stack',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'compensation_stack' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Scope to get pending sagas.
     */
    public function scopePending($query)
    {
        return $query->whereIn('state', [
            'PENDING_PRESCRIPTION',
            'PENDING_INVENTORY_RESERVATION',
            'PENDING_SHIPMENT',
        ]);
    }

    /**
     * Scope to get failed sagas.
     */
    public function scopeFailed($query)
    {
        return $query->where('state', 'FAILED');
    }

    /**
     * Scope to get completed sagas.
     */
    public function scopeCompleted($query)
    {
        return $query->where('state', 'COMPLETED');
    }

    /**
     * Check if saga is in a terminal state.
     */
    public function isTerminal(): bool
    {
        return in_array($this->state, ['COMPLETED', 'FAILED', 'CANCELLED']);
    }

    /**
     * Get duration in seconds.
     */
    public function getDurationSeconds(): ?int
    {
        if (!$this->completed_at) {
            return null;
        }

        return $this->completed_at->diffInSeconds($this->started_at);
    }
}

