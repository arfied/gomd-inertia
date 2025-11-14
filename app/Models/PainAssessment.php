<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PainAssessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'pain_type',
        'pain_type_other',
        'pain_location',
        'pain_location_other',
        'pain_intensity',
        'pain_duration',
        'pain_start',
        'pain_frequency',
        'pain_triggers',
        'pain_triggers_other',
        'pain_relief',
        'pain_relief_other',
    ];

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}
