<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorProfile extends Model
{
    protected $fillable = [
        'specialties',
        'primary_license_state',
        'primary_license_number',
        'dea_number',
        'licensed_states',
        'availability',
        'hourly_rate',
        'lowest_consult_rate',
        'consultation_types',
        'hours_of_availability',
        'interstate_license_registered',
    ];

    protected $casts = [
        'specialties' => 'array',
        'licensed_states' => 'array',
        'consultation_types' => 'array',
        'hours_of_availability' => 'array',
        'interstate_license_registered' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
