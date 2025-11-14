<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentCertificationRequirement extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'certification_id',
        'training_material_id',
    ];

    /**
     * Get the certification that owns the requirement.
     */
    public function certification()
    {
        return $this->belongsTo(AgentCertification::class, 'certification_id');
    }

    /**
     * Get the training material for this requirement.
     */
    public function trainingMaterial()
    {
        return $this->belongsTo(AgentTrainingMaterial::class, 'training_material_id');
    }
}
