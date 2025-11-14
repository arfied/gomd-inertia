<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrescriptionItem extends Model
{
    protected $fillable = [
        'prescription_id',
        'medication_id',
        'custom_medication_name',
        'custom_medication_details',
        'dosage',
        'frequency',
        'duration',
        'quantity',
        'status'
    ];

    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }

    public function medication()
    {
        return $this->belongsTo(Medication::class);
    }

    public function label()
    {
        return $this->hasOne(Label::class);
    }

    public function isCustomMedication()
    {
        return $this->medication_id === null && $this->custom_medication_name !== null;
    }
}
