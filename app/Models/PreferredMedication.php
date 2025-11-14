<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreferredMedication extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'medication_name',
        'taken_before',
        'effectiveness',
    ];

    protected $casts = [
        'taken_before' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
