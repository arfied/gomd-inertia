<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use App\Models\MedicationBase;
use App\Models\Medication;

class Condition extends Model
{
    protected $fillable = ['name', 'therapeutic_use', 'slug', 'description'];

    public $timestamps = false;

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_conditions');
    }

    public function primaryUses(): HasMany
    {
        return $this->hasMany(PrimaryUse::class);
    }

    public function offLabelUses(): HasMany
    {
        return $this->hasMany(OffLabelUse::class);
    }

    public function symptoms()
    {
        return $this->belongsToMany(Symptom::class, 'condition_symptom');
    }

    /**
     * Get the treatments associated with the condition.
     */
    public function treatments(): BelongsToMany
    {
        return $this->belongsToMany(Treatment::class, 'condition_treatment')
            ->withPivot('primary_treatment')
            ->withTimestamps();
    }

    /**
     * Get the medications associated with this condition through primary uses.
     * This is a convenience method to access medications directly from conditions.
     */
    public function medications(): HasManyThrough
    {
        return $this->hasManyThrough(
            MedicationBase::class,
            PrimaryUse::class,
            'condition_id', // Foreign key on PrimaryUse table
            'id', // Foreign key on MedicationBase table
            'id', // Local key on Condition table
            'medication_base_id' // Local key on PrimaryUse table
        );
    }

    /**
     * Get the medications associated with this condition through off-label uses.
     */
    public function offLabelMedications(): HasManyThrough
    {
        return $this->hasManyThrough(
            MedicationBase::class,
            OffLabelUse::class,
            'condition_id', // Foreign key on OffLabelUse table
            'id', // Foreign key on MedicationBase table
            'id', // Local key on Condition table
            'medication_base_id' // Local key on OffLabelUse table
        );
    }

    /**
     * Get all medications associated with this condition (both primary and off-label).
     * This method combines results from both relationships.
     */
    public function allMedications()
    {
        // Get primary medications
        $primaryMeds = $this->medications();

        // Get off-label medications
        $offLabelMeds = $this->offLabelMedications();

        // Combine the queries using a union
        return $primaryMeds->union($offLabelMeds);
    }

    /**
     * Get the medications associated with this condition.
     * Note: This is a direct relationship for the Medication model.
     * For more detailed relationships with MedicationBase, use the medications() method.
     */
    public function directMedications(): BelongsToMany
    {
        return $this->belongsToMany(Medication::class, 'medication_condition')
            ->withPivot('is_primary_use')
            ->withTimestamps();
    }

    /**
     * Get the treatment questions for the condition.
     */
    public function treatmentQuestions(): HasMany
    {
        return $this->hasMany(TreatmentQuestion::class);
    }
}
