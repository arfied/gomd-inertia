<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NonStandardPrescriptionDetail extends Model
{
    protected $fillable = [
        'prescription_id', 'compounding_instructions', 'special_instructions'
    ];

    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }
}
