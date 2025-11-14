<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Allergy extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'allergen',
        'reaction',
        'severity',
        'notes',
    ];

    /**
     * Get the user that owns the allergy.
     */
    public function patient()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the user that owns the allergy.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Severity options
    const SEVERITY_MILD = 'mild';
    const SEVERITY_MODERATE = 'moderate';
    const SEVERITY_SEVERE = 'severe';
    const SEVERITY_LIFE_THREATENING = 'life_threatening';

    // Get severity options for dropdown
    public static function getSeverityOptions()
    {
        return [
            self::SEVERITY_MILD => 'Mild',
            self::SEVERITY_MODERATE => 'Moderate',
            self::SEVERITY_SEVERE => 'Severe',
            self::SEVERITY_LIFE_THREATENING => 'Life-threatening',
        ];
    }
}
