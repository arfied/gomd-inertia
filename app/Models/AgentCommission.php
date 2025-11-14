<?php

namespace App\Models;

use App\Models\Scopes\CommissionTierScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentCommission extends Model
{
    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new CommissionTierScope);
    }

    protected $fillable = [
        'agent_id',
        'upline_agent_id',
        'transaction_id',
        'subscription_id',
        'total_amount',
        'commission_amount',
        'upline_commission_amount',
        'agent_rate',
        'upline_rate',
        'commission_frequency',
        'status',
        'paid_at',
        'payout_id'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'upline_commission_amount' => 'decimal:2',
        'agent_rate' => 'decimal:2',
        'upline_rate' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    /**
     * Get the agent that earned this commission.
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    /**
     * Get the upline agent that earned a cut of this commission.
     */
    public function uplineAgent(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'upline_agent_id');
    }

    /**
     * Get the transaction that generated this commission.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Get the subscription that generated this commission.
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Get the payout this commission belongs to.
     */
    public function payout(): BelongsTo
    {
        return $this->belongsTo(AgentPayout::class, 'payout_id');
    }

    /**
     * Mark this commission as paid.
     */
    public function markAsPaid(): void
    {
        $this->status = 'paid';
        $this->paid_at = now();
        $this->save();
    }

    /**
     * Mark this commission as cancelled.
     */
    public function markAsCancelled(): void
    {
        $this->status = 'cancelled';
        $this->save();
    }

    /**
     * Calculate the total commission amount (agent + upline).
     */
    public function getTotalCommissionAmount(): float
    {
        return $this->commission_amount + $this->upline_commission_amount;
    }
}
