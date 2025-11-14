<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HealthQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'has_chronic_conditions',
        'chronic_conditions',
        'uses_tobacco_alcohol_drugs',
        'substance_use_frequency',
        'is_pregnant',
        'had_recent_surgeries',
        'recent_surgeries_details',
        'has_health_concerns',
        'health_concerns_details',
    ];

    protected $casts = [
        'has_chronic_conditions' => 'boolean',
        'uses_tobacco_alcohol_drugs' => 'boolean',
        'is_pregnant' => 'boolean',
        'had_recent_surgeries' => 'boolean',
        'has_health_concerns' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
