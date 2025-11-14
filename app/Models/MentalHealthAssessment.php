<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MentalHealthAssessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'mental_health_conditions',
        'mental_health_details',
        'suicidal_thoughts',
        'receiving_therapy',
        'benefit_from_counseling',
        'worried_about_counseling',
    ];

    protected $casts = [
        'mental_health_conditions' => 'boolean',
        'suicidal_thoughts' => 'boolean',
        'receiving_therapy' => 'boolean',
        'benefit_from_counseling' => 'boolean',
        'worried_about_counseling' => 'boolean',
    ];

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}
