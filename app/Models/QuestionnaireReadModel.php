<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Read model for questionnaires.
 *
 * This materialized view is updated by event handlers listening to
 * questionnaire events. It provides optimized queries for questionnaire
 * retrieval, filtering, and analytics.
 */
class QuestionnaireReadModel extends Model
{
    public $timestamps = false;
    protected $table = 'questionnaire_read_model';

    protected $fillable = [
        'questionnaire_uuid',
        'title',
        'description',
        'questions_count',
        'status',
        'created_by',
        'patient_id',
        'response_count',
        'completion_rate',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'completion_rate' => 'float',
    ];

    /**
     * Get all active questionnaires.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Get questionnaires for a specific patient.
     */
    public function scopeForPatient($query, string $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    /**
     * Get questionnaires created by a specific user.
     */
    public function scopeCreatedBy($query, string $userId)
    {
        return $query->where('created_by', $userId);
    }
}

