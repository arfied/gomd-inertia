<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionOption extends Model
{
    protected $fillable = ['question_id', 'option_value', 'order'];

    protected $casts = [
        'option_value' => 'array',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
