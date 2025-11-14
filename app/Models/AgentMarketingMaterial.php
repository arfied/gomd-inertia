<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentMarketingMaterial extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'type',
        'file_path',
        'template_content',
        'template_variables',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'template_variables' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the usage records for this marketing material.
     */
    public function usage()
    {
        return $this->hasMany(AgentMarketingUsage::class, 'marketing_material_id');
    }

    /**
     * Get the total usage count for this marketing material.
     */
    public function getTotalUsageCountAttribute()
    {
        return $this->usage()->count();
    }

    /**
     * Get the total click count for this marketing material.
     */
    public function getTotalClickCountAttribute()
    {
        return $this->usage()->sum('click_count');
    }

    /**
     * Get the total conversion count for this marketing material.
     */
    public function getTotalConversionCountAttribute()
    {
        return $this->usage()->sum('conversion_count');
    }

    /**
     * Get the conversion rate for this marketing material.
     */
    public function getConversionRateAttribute()
    {
        $clicks = $this->total_click_count;
        $conversions = $this->total_conversion_count;

        if ($clicks === 0) {
            return 0;
        }

        return round(($conversions / $clicks) * 100, 2);
    }
}
