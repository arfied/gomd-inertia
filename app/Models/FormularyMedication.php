<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormularyMedication extends Model
{
    protected $table = 'formulary_medications';

    protected $fillable = [
        'formulary_id',
        'medication_id',
        'tier',
        'requires_pre_authorization',
        'notes',
    ];

    protected $casts = [
        'requires_pre_authorization' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the formulary this medication belongs to.
     */
    public function formulary(): BelongsTo
    {
        return $this->belongsTo(Formulary::class);
    }

    /**
     * Get the medication.
     */
    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }

    /**
     * Scope to filter by tier.
     */
    public function scopeByTier($query, $tier)
    {
        return $query->where('tier', $tier);
    }

    /**
     * Scope to filter medications requiring pre-authorization.
     */
    public function scopeRequiresPreAuth($query)
    {
        return $query->where('requires_pre_authorization', true);
    }
}

