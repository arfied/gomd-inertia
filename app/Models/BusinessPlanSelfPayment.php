<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessPlanSelfPayment extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_REFUNDED = 'refunded';

    const STATUSES = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_COMPLETED => 'Completed',
        self::STATUS_FAILED => 'Failed',
        self::STATUS_REFUNDED => 'Refunded',
    ];

    protected $fillable = [
        'business_plan_id',
        'business_employee_id',
        'user_id',
        'transaction_id',
        'amount_paid',
        'payment_method',
        'status',
        'paid_at',
        'transaction_reference',
        'notes',
        'meta_data',
    ];

    protected $casts = [
        'amount_paid' => 'integer',
        'paid_at' => 'datetime',
        'meta_data' => 'array',
    ];

    /**
     * Get the business plan that this self-payment belongs to.
     */
    public function businessPlan(): BelongsTo
    {
        return $this->belongsTo(BusinessPlan::class);
    }

    /**
     * Get the business employee who made this self-payment.
     */
    public function businessEmployee(): BelongsTo
    {
        return $this->belongsTo(BusinessEmployee::class);
    }

    /**
     * Get the user who made this self-payment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the transaction associated with this self-payment.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Check if the self-payment is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if the self-payment is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if the self-payment failed.
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Check if the self-payment was refunded.
     */
    public function isRefunded(): bool
    {
        return $this->status === self::STATUS_REFUNDED;
    }

    /**
     * Mark the self-payment as completed.
     */
    public function markAsCompleted(string $transactionReference = null): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'paid_at' => now(),
            'transaction_reference' => $transactionReference,
        ]);
    }

    /**
     * Mark the self-payment as failed.
     */
    public function markAsFailed(string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'notes' => $reason,
        ]);
    }

    /**
     * Get the amount in dollars.
     */
    public function getAmountInDollarsAttribute(): float
    {
        return $this->amount_paid / 100;
    }

    /**
     * Get the status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? 'Unknown';
    }
}
