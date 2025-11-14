<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicationScreening extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'celecoxib_bleeding',
        'celecoxib_kidney',
        'duloxetine_liver',
        'duloxetine_maois',
        'baclofen_kidney',
        'cyclobenzaprine_cardio',
        'methocarbamol_liver',
        'meloxicam_bleeding',
        'meloxicam_kidney',
        'amitriptyline_cardio',
        'amitriptyline_maois',
        'etodolac_bleeding',
        'etodolac_liver',
        'venlafaxine_liver',
        'venlafaxine_maois',
        'tizanidine_liver',
        'tizanidine_cns',
        'orphenadrine_liver',
        'nabumetone_bleeding',
        'nabumetone_liver',
        'nortriptyline_cardio',
        'nortriptyline_maois',
    ];

    protected $casts = [
        'celecoxib_bleeding' => 'boolean',
        'celecoxib_kidney' => 'boolean',
        'duloxetine_liver' => 'boolean',
        'duloxetine_maois' => 'boolean',
        'baclofen_kidney' => 'boolean',
        'cyclobenzaprine_cardio' => 'boolean',
        'methocarbamol_liver' => 'boolean',
        'meloxicam_bleeding' => 'boolean',
        'meloxicam_kidney' => 'boolean',
        'amitriptyline_cardio' => 'boolean',
        'amitriptyline_maois' => 'boolean',
        'etodolac_bleeding' => 'boolean',
        'etodolac_liver' => 'boolean',
        'venlafaxine_liver' => 'boolean',
        'venlafaxine_maois' => 'boolean',
        'tizanidine_liver' => 'boolean',
        'tizanidine_cns' => 'boolean',
        'orphenadrine_liver' => 'boolean',
        'nabumetone_bleeding' => 'boolean',
        'nabumetone_liver' => 'boolean',
        'nortriptyline_cardio' => 'boolean',
        'nortriptyline_maois' => 'boolean',
    ];

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}
