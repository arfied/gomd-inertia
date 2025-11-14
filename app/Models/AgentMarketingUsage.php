<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentMarketingUsage extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'agent_id',
        'marketing_material_id',
        'usage_type',
        'click_count',
        'conversion_count',
    ];

    /**
     * Get the agent that used the marketing material.
     */
    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    /**
     * Get the marketing material that was used.
     */
    public function marketingMaterial()
    {
        return $this->belongsTo(AgentMarketingMaterial::class, 'marketing_material_id');
    }

    /**
     * Get the conversion rate for this usage.
     */
    public function getConversionRateAttribute()
    {
        if ($this->click_count === 0) {
            return 0;
        }

        return round(($this->conversion_count / $this->click_count) * 100, 2);
    }

    /**
     * Increment the click count.
     */
    public function incrementClickCount($count = 1)
    {
        $this->increment('click_count', $count);

        return $this;
    }

    /**
     * Increment the conversion count.
     */
    public function incrementConversionCount($count = 1)
    {
        $this->increment('conversion_count', $count);

        return $this;
    }
}
