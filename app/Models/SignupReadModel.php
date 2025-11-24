<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Read model for signups.
 *
 * This materialized view is updated by event handlers listening to
 * signup events. It provides optimized queries for signup state tracking,
 * progress monitoring, and analytics.
 */
class SignupReadModel extends Model
{
    public $timestamps = false;
    protected $table = 'signup_read_model';

    protected $fillable = [
        'signup_uuid',
        'user_id',
        'signup_path',
        'medication_name',
        'condition_id',
        'plan_id',
        'questionnaire_responses',
        'payment_id',
        'payment_amount',
        'payment_status',
        'subscription_id',
        'status',
        'failure_reason',
        'failure_message',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'medication_name' => 'json',
        'questionnaire_responses' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get signups for a specific user.
     */
    public function scopeForUser($query, string $userId)
    {
        return $query->where('user_id', $userId)->orderBy('created_at', 'desc');
    }

    /**
     * Get signups with a specific status.
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Get completed signups.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Get pending signups.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Get failed signups.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Get signups by signup path.
     */
    public function scopeByPath($query, string $path)
    {
        return $query->where('signup_path', $path);
    }

    /**
     * Get signups with a specific plan.
     */
    public function scopeWithPlan($query, string $planId)
    {
        return $query->where('plan_id', $planId);
    }

    /**
     * Get signups with a specific medication.
     */
    public function scopeWithMedication($query, string $medicationName)
    {
        return $query->whereJsonContains('medication_name', $medicationName);
    }

    /**
     * Get signups with a specific condition.
     */
    public function scopeWithCondition($query, string $conditionId)
    {
        return $query->where('condition_id', $conditionId);
    }
}

