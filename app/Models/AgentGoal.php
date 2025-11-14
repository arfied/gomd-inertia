<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Commission;
use App\Models\Referral;

class AgentGoal extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'agent_id',
        'type',
        'target_value',
        'period_type',
        'start_date',
        'end_date',
        'is_achieved',
        'achieved_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'target_value' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_achieved' => 'boolean',
        'achieved_at' => 'datetime',
    ];

    /**
     * Get the agent that owns the goal.
     */
    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    /**
     * Get the current progress towards the goal.
     */
    public function getCurrentProgressAttribute()
    {
        $agent = $this->agent;

        if (!$agent) {
            return 0;
        }

        switch ($this->type) {
            case 'referrals':
                return $this->getReferralProgress();
            case 'commissions':
                return $this->getCommissionProgress();
            default:
                return 0;
        }
    }

    /**
     * Get the progress percentage towards the goal.
     */
    public function getProgressPercentageAttribute()
    {
        if ($this->target_value <= 0) {
            return 0;
        }

        $percentage = ($this->current_progress / $this->target_value) * 100;

        return min(100, round($percentage, 2));
    }

    /**
     * Get the referral progress.
     */
    protected function getReferralProgress()
    {
        return Referral::where('referring_agent_id', $this->agent_id)
                       ->whereBetween('created_at', [$this->start_date, $this->end_date])
                       ->count();
    }

    /**
     * Get the commission progress.
     */
    protected function getCommissionProgress()
    {
        return Commission::where('agent_id', $this->agent_id)
                         ->where('status', 'paid')
                         ->whereBetween('created_at', [$this->start_date, $this->end_date])
                         ->sum('amount');
    }

    /**
     * Check if the goal is achieved and update if necessary.
     */
    public function checkAchievement()
    {
        if (!$this->is_achieved && $this->progress_percentage >= 100) {
            $this->is_achieved = true;
            $this->achieved_at = now();
            $this->save();

            // Trigger achievement notification
            $this->agent->user->notify(new \App\Notifications\GoalAchievedNotification($this));
        }

        return $this;
    }

    /**
     * Scope a query to only include active goals.
     */
    public function scopeActive($query)
    {
        $now = now()->format('Y-m-d');

        return $query->where('start_date', '<=', $now)
                     ->where('end_date', '>=', $now);
    }

    /**
     * Scope a query to only include achieved goals.
     */
    public function scopeAchieved($query)
    {
        return $query->where('is_achieved', true);
    }

    /**
     * Scope a query to only include unachieved goals.
     */
    public function scopeUnachieved($query)
    {
        return $query->where('is_achieved', false);
    }
}
