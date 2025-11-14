<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LifestyleHabit extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'alcohol_use',
        'alcohol_details',
        'tobacco_use',
        'tobacco_details',
        'drug_use',
        'drug_details',
        'exercise_frequency',
    ];

    protected $casts = [
        'alcohol_use' => 'boolean',
        'tobacco_use' => 'boolean',
        'drug_use' => 'boolean',
    ];

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}
