<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicationClass extends Model
{
    protected $fillable = ['name', 'description', 'parent_id'];

    public $timestamps = false;

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MedicationClass::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(MedicationClass::class, 'parent_id');
    }

    public function medicationBases(): HasMany
    {
        return $this->hasMany(MedicationBase::class);
    }
}
