<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class AgentSupportTicketReply extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ticket_id',
        'user_id',
        'message',
        'is_internal',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_internal' => 'boolean',
    ];

    /**
     * Get the ticket that owns the reply.
     */
    public function ticket()
    {
        return $this->belongsTo(AgentSupportTicket::class, 'ticket_id');
    }

    /**
     * Get the user that created the reply.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include public replies.
     */
    public function scopePublic($query)
    {
        return $query->where('is_internal', false);
    }

    /**
     * Scope a query to only include internal replies.
     */
    public function scopeInternal($query)
    {
        return $query->where('is_internal', true);
    }
}
