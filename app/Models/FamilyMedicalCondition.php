<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FamilyMedicalCondition extends Model
{
    use HasFactory;

    protected $fillable = [
        'family_medical_history_id',
        'name',
    ];

    public function familyMedicalHistory()
    {
        return $this->belongsTo(FamilyMedicalHistory::class);
    }
}
