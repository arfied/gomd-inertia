<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class AgentSupportTicket extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'agent_id',
        'subject',
        'description',
        'status',
        'assigned_to',
        'resolved_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    /**
     * Get the agent that created the ticket.
     */
    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    /**
     * Get the user assigned to the ticket.
     */
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the replies for this ticket.
     */
    public function replies()
    {
        return $this->hasMany(AgentSupportTicketReply::class, 'ticket_id');
    }

    /**
     * Get the latest reply for this ticket.
     */
    public function latestReply()
    {
        return $this->hasOne(AgentSupportTicketReply::class, 'ticket_id')
                    ->latest();
    }

    /**
     * Determine if the ticket is open.
     */
    public function isOpen()
    {
        return $this->status === 'open' || $this->status === 'in_progress';
    }

    /**
     * Determine if the ticket is resolved.
     */
    public function isResolved()
    {
        return $this->status === 'resolved' || $this->status === 'closed';
    }

    /**
     * Mark the ticket as resolved.
     */
    public function markAsResolved($userId = null)
    {
        $this->status = 'resolved';
        $this->resolved_at = now();

        if ($userId !== null) {
            $this->assigned_to = $userId;
        }

        $this->save();

        return $this;
    }

    /**
     * Scope a query to only include open tickets.
     */
    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'in_progress']);
    }

    /**
     * Scope a query to only include resolved tickets.
     */
    public function scopeResolved($query)
    {
        return $query->whereIn('status', ['resolved', 'closed']);
    }

    /**
     * Scope a query to only include tickets for a specific agent.
     */
    public function scopeForAgent($query, $agentId)
    {
        return $query->where('agent_id', $agentId);
    }

    /**
     * Scope a query to only include tickets assigned to a specific user.
     */
    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }
}
