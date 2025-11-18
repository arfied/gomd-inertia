<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicationSearchIndex extends Model
{
    use HasFactory;
    protected $table = 'medication_search_index';

    protected $fillable = [
        'medication_id',
        'name',
        'generic_name',
        'drug_class',
        'description',
        'type',
        'status',
        'unit_price',
        'requires_prescription',
        'controlled_substance',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'requires_prescription' => 'boolean',
        'controlled_substance' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the medication this index entry represents.
     */
    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to filter by drug class.
     */
    public function scopeByDrugClass($query, $drugClass)
    {
        return $query->where('drug_class', $drugClass);
    }

    /**
     * Scope to filter by prescription requirement.
     */
    public function scopeRequiresPrescription($query, $requires = true)
    {
        return $query->where('requires_prescription', $requires);
    }
}

