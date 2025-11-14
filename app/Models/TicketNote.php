<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TicketNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'support_ticket_id',
        'user_id',
        'note',
        'is_internal',
        'metadata',
    ];

    protected $casts = [
        'is_internal' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Get the support ticket this note belongs to.
     */
    public function supportTicket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class);
    }

    /**
     * Get the user who created this note.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for public notes.
     */
    public function scopePublic($query)
    {
        return $query->where('is_internal', false);
    }

    /**
     * Scope for internal notes.
     */
    public function scopeInternal($query)
    {
        return $query->where('is_internal', true);
    }
}
