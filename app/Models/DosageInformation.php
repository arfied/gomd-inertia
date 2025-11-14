<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DosageInformation extends Model
{
    protected $fillable = [
        'medication_base_id',
        'use_type',
        'starting_dose',
        'other_dosages'
    ];

    public $timestamps = false;

    public function medicationBase(): BelongsTo
    {
        return $this->belongsTo(MedicationBase::class);
    }
}
