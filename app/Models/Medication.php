<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Medication extends Model
{
    protected $fillable = [
        'name',
        'type',
        'drug_class',
        'generic_name',
        'description',
        'dosage_form',
        'route_of_administration',
        'strength',
        'half_life',
        'manufacturer',
        'ndc_number',
        'unit_price',
        'requires_prescription',
        'controlled_substance',
        'storage_conditions',
        'contraindications',
        'side_effects',
        'interactions',
        'pregnancy_category',
        'breastfeeding_safe',
        'black_box_warning',
        'status',
        'is_usual_dosage',
        'order'
    ];

    protected $casts = [
        'requires_prescription' => 'boolean',
        'controlled_substance' => 'boolean',
        'unit_price' => 'decimal:2',
        'breastfeeding_safe' => 'boolean',
        'is_usual_dosage' => 'boolean',
    ];

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function prescriptionItems()
    {
        return $this->hasMany(PrescriptionItem::class);
    }

    /**
     * Get the conditions associated with this medication.
     * Note: This is a direct relationship for the Medication model.
     * For more detailed relationships, use the MedicationBase model.
     */
    public function conditions(): BelongsToMany
    {
        return $this->belongsToMany(Condition::class, 'medication_condition')
            ->withPivot('is_primary_use')
            ->withTimestamps();
    }
}
