<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class MedicationBase extends Model
{
    protected $fillable = [
        'generic_name',
        'medication_class_id',
        'type',
        'description',
        'requires_prescription',
        'controlled_substance',
        'contraindications',
        'side_effects',
        'interactions',
        'pregnancy_category',
        'breastfeeding_safe',
        'black_box_warning',
        'status'
    ];

    protected $casts = [
        'requires_prescription' => 'boolean',
        'controlled_substance' => 'boolean',
        'breastfeeding_safe' => 'boolean',
    ];

    public $timestamps = false;

    public function medicationClass(): BelongsTo
    {
        return $this->belongsTo(MedicationClass::class);
    }

    public function medicationVariants(): HasMany
    {
        return $this->hasMany(MedicationVariant::class);
    }

    public function getUsualVariantAttribute(): ?MedicationVariant
    {
        return $this->medicationVariants()->where('is_usual_dosage', true)->first();
    }

    public function primaryUses(): HasMany
    {
        return $this->hasMany(PrimaryUse::class);
    }

    public function offLabelUses(): HasMany
    {
        return $this->hasMany(OffLabelUse::class);
    }

    public function dosageInformation(): HasMany
    {
        return $this->hasMany(DosageInformation::class);
    }

    public function scopeIsActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Get conditions associated with this medication through primary uses.
     */
    public function primaryConditions(): HasManyThrough
    {
        return $this->hasManyThrough(
            Condition::class,
            PrimaryUse::class,
            'medication_base_id', // Foreign key on PrimaryUse table
            'id', // Foreign key on Condition table
            'id', // Local key on MedicationBase table
            'condition_id' // Local key on PrimaryUse table
        );
    }

    /**
     * Get conditions associated with this medication through off-label uses.
     */
    public function offLabelConditions(): HasManyThrough
    {
        return $this->hasManyThrough(
            Condition::class,
            OffLabelUse::class,
            'medication_base_id', // Foreign key on OffLabelUse table
            'id', // Foreign key on Condition table
            'id', // Local key on MedicationBase table
            'condition_id' // Local key on OffLabelUse table
        );
    }

    /**
     * Get all conditions associated with this medication (both primary and off-label).
     * This method combines results from both relationships.
     */
    public function conditions()
    {
        // Get primary conditions
        $primaryConditions = $this->primaryConditions();

        // Get off-label conditions
        $offLabelConditions = $this->offLabelConditions();

        // Combine the queries using a union
        return $primaryConditions->union($offLabelConditions);
    }
}
