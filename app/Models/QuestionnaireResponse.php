<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionnaireResponse extends Model
{
    protected $table = 'questionnaire_responses';

    protected $fillable = [
        'response_uuid',
        'questionnaire_uuid',
        'patient_id',
        'responses',
        'metadata',
        'submitted_at',
    ];

    protected $casts = [
        'responses' => 'array',
        'metadata' => 'array',
        'submitted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the questionnaire this response belongs to.
     */
    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(
            QuestionnaireReadModel::class,
            'questionnaire_uuid',
            'questionnaire_uuid'
        );
    }

    /**
     * Get the patient who submitted this response.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id', 'id');
    }
}

