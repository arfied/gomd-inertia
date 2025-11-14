<?php

namespace App\Models;

use App\Concerns\FormatsDateAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalCondition extends Model
{
    use HasFactory, FormatsDateAttributes;

    protected $fillable = [
        'patient_id',
        'condition_name',
        'diagnosed_at',
        'notes',
        'condition_id',
        'status',
        'is_custom',
        'had_condition_before',
        'is_chronic',
        'symtom_start_date',
    ];

    protected $casts = [
        'had_condition_before' => 'boolean',
        'is_chronic' => 'boolean',
        'is_custom' => 'boolean',
        // Removed date casting for diagnosed_at to prevent timezone issues
        'symtom_start_date' => 'date',
    ];

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function condition()
    {
        return $this->belongsTo(Condition::class, 'condition_id');
    }
}
