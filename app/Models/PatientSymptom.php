<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientSymptom extends Model
{
    protected $table = 'patient_symptom';

    public $timestamps = false;

    protected $fillable = [
        'patient_id',
        'symptom',
    ];
}
