<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentCertification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'required_training_count',
        'requires_approval',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'requires_approval' => 'boolean',
    ];

    /**
     * Get the certification requirements for this certification.
     */
    public function requirements()
    {
        return $this->hasMany(AgentCertificationRequirement::class, 'certification_id');
    }

    /**
     * Get the training materials required for this certification.
     */
    public function trainingMaterials()
    {
        return $this->belongsToMany(
            AgentTrainingMaterial::class,
            'agent_certification_requirements',
            'certification_id',
            'training_material_id'
        );
    }

    /**
     * Get the earned certifications for this certification.
     */
    public function earnedCertifications()
    {
        return $this->hasMany(AgentEarnedCertification::class, 'certification_id');
    }

    /**
     * Get the agents who have earned this certification.
     */
    public function agents()
    {
        return $this->belongsToMany(
            Agent::class,
            'agent_earned_certifications',
            'certification_id',
            'agent_id'
        )->withPivot('earned_at', 'expires_at', 'status');
    }
}
