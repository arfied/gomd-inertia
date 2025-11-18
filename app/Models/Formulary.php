<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Formulary extends Model
{
    use HasFactory;
    protected $fillable = [
        'uuid',
        'name',
        'description',
        'organization_id',
        'type',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the medications in this formulary.
     */
    public function medications(): BelongsToMany
    {
        return $this->belongsToMany(Medication::class, 'formulary_medications')
            ->withPivot('tier', 'requires_pre_authorization', 'notes')
            ->withTimestamps();
    }

    /**
     * Scope to filter by status.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to filter by organization.
     */
    public function scopeByOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    /**
     * Scope to filter by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}

