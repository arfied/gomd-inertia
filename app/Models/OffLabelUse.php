<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OffLabelUse extends Model
{
    protected $fillable = [
        'medication_base_id',
        'condition_id',
        'primary_treatment',
        'mechanism_of_action'
    ];

    public $timestamps = false;

    public function medicationBase(): BelongsTo
    {
        return $this->belongsTo(MedicationBase::class);
    }

    public function condition(): BelongsTo
    {
        return $this->belongsTo(Condition::class);
    }
}
