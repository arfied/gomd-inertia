<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'name',
        'subtitle',
        'description',
        'is_active',
    ];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}
