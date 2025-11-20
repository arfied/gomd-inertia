<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Read model for subscription analytics.
 *
 * This materialized view is updated by event handlers listening to
 * subscription and payment events. It provides optimized queries for
 * MRR, churn, and LTV calculations.
 */
class SubscriptionAnalyticsView extends Model
{
    public $timestamps = false;
    protected $table = 'subscription_analytics_view';

    protected $fillable = [
        'subscription_id',
        'user_id',
        'plan_id',
        'plan_name',
        'monthly_price',
        'status',
        'started_at',
        'ended_at',
        'cancelled_at',
        'total_revenue',
        'months_active',
        'churn_reason',
        'is_trial',
        'last_payment_date',
        'next_payment_date',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'last_payment_date' => 'datetime',
        'next_payment_date' => 'datetime',
        'monthly_price' => 'decimal:2',
        'total_revenue' => 'decimal:2',
        'is_trial' => 'boolean',
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }
}

