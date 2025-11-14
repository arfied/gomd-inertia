<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Symptom extends Model
{
    public function conditions()
    {
        return $this->belongsToMany(Condition::class, 'condition_symptom');
    }
}
