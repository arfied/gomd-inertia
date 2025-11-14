<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PsychologicalSocialFactor extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'stress_levels',
        'support_system',
        'work_environment',
        'mental_health_changes ',
    ];

    protected $casts = [
        'support_system' => 'boolean',
        'work_environment' => 'boolean',
    ];

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}
