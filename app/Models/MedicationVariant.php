<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicationVariant extends Model
{
    protected $fillable = [
        'brand_name',
        'manufacturer',
        'medication_base_id',
        'strength',
        'dosage_form',
        'route_of_administration',
        'ndc_number',
        'unit_price',
        'storage_conditions',
        'is_usual_dosage',
        'order_index'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'is_usual_dosage' => 'boolean',
        'order_index' => 'integer',
    ];

    public $timestamps = false;

    public function medicationBase(): BelongsTo
    {
        return $this->belongsTo(MedicationBase::class);
    }
}
