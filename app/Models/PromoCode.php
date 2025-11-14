<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'type', 'value', 'start_date', 'end_date', 'is_active', 'usage_limit', 'times_used'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function subscriptionPlans()
    {
        return $this->belongsToMany(SubscriptionPlan::class);
    }

    public function isValid()
    {
        return $this->is_active &&
               $this->start_date <= now() &&
               $this->end_date >= now() &&
               ($this->usage_limit === null || $this->times_used < $this->usage_limit);
    }
}
