<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentTrainingMaterial extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'type',
        'file_path',
        'external_url',
        'is_required',
        'order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_required' => 'boolean',
    ];

    /**
     * Get the training progress records for this material.
     */
    public function progress()
    {
        return $this->hasMany(AgentTrainingProgress::class, 'training_material_id');
    }

    /**
     * Get the certification requirements that include this material.
     */
    public function certificationRequirements()
    {
        return $this->hasMany(AgentCertificationRequirement::class, 'training_material_id');
    }

    /**
     * Get the certifications that require this material.
     */
    public function certifications()
    {
        return $this->belongsToMany(
            AgentCertification::class,
            'agent_certification_requirements',
            'training_material_id',
            'certification_id'
        );
    }
}
