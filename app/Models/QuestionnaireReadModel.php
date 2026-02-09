<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Read model for questionnaires.
 *
 * This materialized view is updated by event handlers listening to
 * questionnaire events. It provides optimized queries for questionnaire
 * retrieval, filtering, and analytics.
 *
 * Note: Questionnaire responses are now stored in a separate questionnaire_responses
 * table to support multiple responses per questionnaire.
 */
class QuestionnaireReadModel extends Model
{
    public $timestamps = false;
    protected $table = 'questionnaire_read_model';

    protected $fillable = [
        'questionnaire_uuid',
        'title',
        'description',
        'questions',
        'questions_count',
        'status',
        'created_by',
        'response_count',
        'completion_rate',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'questions' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'completion_rate' => 'float',
    ];

    /**
     * Get all responses for this questionnaire.
     */
    public function responses(): HasMany
    {
        return $this->hasMany(
            QuestionnaireResponse::class,
            'questionnaire_uuid',
            'questionnaire_uuid'
        );
    }

    /**
     * Get all active questionnaires.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Get questionnaires created by a specific user.
     */
    public function scopeCreatedBy($query, string $userId)
    {
        return $query->where('created_by', $userId);
    }
}

