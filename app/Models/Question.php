<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = ['service_id', 'question', 'type', 'options', 'required', 'order'];

    protected $casts = [
        'required' => 'boolean',
        'parent_answer_value' => 'array',
    ];

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    public function parent()
    {
        return $this->belongsTo(Question::class, 'parent_question_id');
    }

    public function children()
    {
        return $this->hasMany(Question::class, 'parent_question_id');
    }

    public function options()
    {
        return $this->hasMany(QuestionOption::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
