<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Insurance extends Model
{
    protected $fillable = [
        'user_id',
        'provider_name',
        'policy_number',
        'group_number',
        'plan_type',
        'coverage_start_date',
        'coverage_end_date',
        'copay_amount',
        'deductible_amount',
        'coverage_limit',
        'prior_authorization_required',
        'formulary_type',
        'status',
    ];

    protected $casts = [
        'coverage_start_date' => 'date',
        'coverage_end_date' => 'date',
        'copay_amount' => 'decimal:2',
        'deductible_amount' => 'decimal:2',
        'coverage_limit' => 'decimal:2',
        'prior_authorization_required' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function claims(): HasMany
    {
        return $this->hasMany(InsuranceClaim::class);
    }
}
