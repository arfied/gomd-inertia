<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentLandingPage extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'agent_id',
        'title',
        'slug',
        'content',
        'settings',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the agent that owns the landing page.
     */
    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    /**
     * Get the URL for the landing page.
     */
    public function getUrlAttribute()
    {
        return url('/landing/' . $this->slug);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($landingPage) {
            // Generate a slug if one wasn't provided
            if (empty($landingPage->slug)) {
                $landingPage->slug = \Illuminate\Support\Str::slug($landingPage->title) . '-' . \Illuminate\Support\Str::random(5);
            }
        });
    }
}
