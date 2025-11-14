<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Treatment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'features',
        'price',
        'is_active',
        'display_order',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    /**
     * Get the conditions associated with the treatment.
     */
    public function conditions(): BelongsToMany
    {
        return $this->belongsToMany(Condition::class, 'condition_treatment')
            ->withPivot('primary_treatment')
            ->withTimestamps();
    }

    /**
     * Get the questions for the treatment.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(TreatmentQuestion::class)->orderBy('display_order');
    }

    /**
     * Scope a query to only include active treatments.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('status', 'active');
    }
}
