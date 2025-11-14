<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FamilyMedicalHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'chronic_pain',
        'chronic_pain_details',
    ];

    protected $casts = [
        'chronic_pain' => 'boolean',
    ];

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function familyMedicalConditions()
    {
        return $this->hasMany(FamilyMedicalCondition::class);
    }
}
