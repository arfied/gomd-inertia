<?php

namespace App\Models;

use App\Services\HeightConverterService;
use Illuminate\Database\Eloquent\Model;

class UserMeasurement extends Model
{
    protected $fillable = [
        'user_id',
        'height', // in inches
        'weight', // in pounds
        'measured_at'
    ];

    protected $casts = [
        'measured_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the formatted height as feet and inches (e.g., "5'11\"")
     */
    public function getFormattedHeightAttribute(): string
    {
        if (!$this->height) {
            return 'N/A';
        }

        $heightConverter = app(HeightConverterService::class);
        return $heightConverter->inchesToFeetInches((int) $this->height);
    }
}
