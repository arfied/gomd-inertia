<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiagnosticTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'details',
        'physiotherapy_details ',
    ];

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}
