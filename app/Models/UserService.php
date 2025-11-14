<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserService extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'service_id', 'status', 'video_path'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isInProgress()
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isAwaitingPayment()
    {
        return $this->status === 'awaiting_payment';
    }

    public function isPaid()
    {
        return $this->status === 'paid';
    }

    public function isUnderReview()
    {
        return $this->status === 'under_review';
    }

    public function isAdditionalInfoRequired()
    {
        return $this->status === 'additional_info_required';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isDenied()
    {
        return $this->status === 'denied';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function isExpired()
    {
        return $this->status === 'expired';
    }
}
