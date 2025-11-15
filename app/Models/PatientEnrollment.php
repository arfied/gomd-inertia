<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientEnrollment extends Model
{
    use HasFactory;

    protected $table = 'patient_enrollments';

    protected $fillable = [
        'patient_uuid',
        'user_id',
        'source',
        'metadata',
        'enrolled_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'enrolled_at' => 'datetime',
    ];
}

