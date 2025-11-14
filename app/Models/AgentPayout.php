<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AgentPayout extends Model
{
    /**
     * Valid payout methods
     */
    const PAYOUT_METHODS = [
        'bank_transfer' => 'Bank Transfer',
        'check' => 'Check',
        'paypal' => 'PayPal',
        'stripe' => 'Stripe'
    ];

    /**
     * Valid payout statuses
     */
    const STATUSES = [
        'pending' => 'Pending',
        'processed' => 'Processed',
        'failed' => 'Failed',
        'cancelled' => 'Cancelled'
    ];

    protected $table = 'agent_payouts';

    protected $fillable = [
        'agent_id',
        'total_amount',
        'commission_count',
        'payout_method',
        'status',
        'processed_at',
        'processed_by',
        'reference_number',
        'payment_reference',
        'notes',
        'payment_details'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'processed_at' => 'datetime',
        'payment_details' => 'array'
    ];

    /**
     * Get the agent this payout belongs to.
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    /**
     * Get the user who processed this payout.
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Get the commissions included in this payout.
     */
    public function commissions(): HasMany
    {
        return $this->hasMany(AgentCommission::class, 'payout_id');
    }

    /**
     * Mark payout as processed.
     */
    public function markAsProcessed(int $processedBy, string $paymentReference = null): void
    {
        $this->update([
            'status' => 'processed',
            'processed_at' => now(),
            'processed_by' => $processedBy,
            'payment_reference' => $paymentReference
        ]);
    }

    /**
     * Mark payout as failed.
     */
    public function markAsFailed(string $reason = null): void
    {
        $this->update([
            'status' => 'failed',
            'notes' => $reason ? $this->notes . "\nFailure reason: " . $reason : $this->notes
        ]);
    }

    /**
     * Mark payout as cancelled.
     */
    public function markAsCancelled(string $reason = null): void
    {
        $this->update([
            'status' => 'cancelled',
            'notes' => $reason ? $this->notes . "\nCancellation reason: " . $reason : $this->notes
        ]);
    }

    /**
     * Check if payout is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if payout is processed.
     */
    public function isProcessed(): bool
    {
        return $this->status === 'processed';
    }

    /**
     * Check if payout is failed.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if payout is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Get the payout method label.
     */
    public function getPayoutMethodLabel(): string
    {
        return self::PAYOUT_METHODS[$this->payout_method] ?? $this->payout_method;
    }

    /**
     * Get the status label.
     */
    public function getStatusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Scope to filter by agent.
     */
    public function scopeForAgent($query, int $agentId)
    {
        return $query->where('agent_id', $agentId);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by payout method.
     */
    public function scopeWithPayoutMethod($query, string $method)
    {
        return $query->where('payout_method', $method);
    }

    /**
     * Scope for pending payouts.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for processed payouts.
     */
    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }

    /**
     * Scope for failed payouts.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for recent payouts.
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Get formatted total amount.
     */
    public function getFormattedTotalAmountAttribute(): string
    {
        return '$' . number_format($this->total_amount, 2);
    }

    /**
     * Generate a unique reference number.
     */
    public static function generateReferenceNumber(): string
    {
        return 'PO-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
    }
}
