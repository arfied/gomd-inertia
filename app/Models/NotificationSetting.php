<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'agent_id',
        'referral_notifications',
        'commission_notifications',
        'payment_notifications',
        'status_notifications',
        'notification_frequency',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'referral_notifications' => 'boolean',
        'commission_notifications' => 'boolean',
        'payment_notifications' => 'boolean',
        'status_notifications' => 'boolean',
    ];

    /**
     * Get the agent that owns the notification settings.
     */
    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }
}
