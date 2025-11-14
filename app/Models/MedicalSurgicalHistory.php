<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalSurgicalHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'past_injuries',
        'past_injuries_details',
        'surgery',
        'surgery_details',
        'chronic_conditions_details',
    ];

    protected $casts = [
        'past_injuries' => 'boolean',
        'surgery' => 'boolean',
    ];

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}
