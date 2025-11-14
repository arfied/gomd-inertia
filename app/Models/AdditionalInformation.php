<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdditionalInformation extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'additional_concerns',
        'daily_activities_impact',
        'sleep_impact',
        'mobility_impact',
        'emotional_impact',
        'associated_symptoms',
        'systemic_symptoms',
    ];

    protected $casts = [
        'systemic_symptoms' => 'boolean',
    ];

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}
