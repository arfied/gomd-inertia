<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhysicalExaminationIndicator extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'tenderness',
        'difficulty_moving ',
        'reduced_activity',
    ];

    protected $casts = [
        'tenderness' => 'boolean',
        'difficulty_moving ' => 'boolean',
        'reduced_activity ' => 'boolean',
    ];

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}
