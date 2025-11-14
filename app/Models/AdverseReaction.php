<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdverseReaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_reported_medication_id',
        'reaction_details',
        'reaction_date',
        'severity',
    ];

    protected $casts = [
        'reaction_date' => 'date',
    ];

    public function userReportedMedication()
    {
        return $this->belongsTo(UserReportedMedication::class);
    }
}
