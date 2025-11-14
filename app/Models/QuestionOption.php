<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionOption extends Model
{
    protected $casts = [
        'option_value' => 'array',
    ];

    public function service()
    {
        return $this->belongsTo(Question::class);
    }
}
