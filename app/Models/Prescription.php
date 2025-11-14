<?php

namespace App\Models;

use App\Concerns\FormatsDateAttributes;
use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    use FormatsDateAttributes;
    protected $fillable = ['user_id', 'doctor_id', 'pharmacist_id', 'status', 'notes', 'is_non_standard'];

    protected $casts = [
        'is_non_standard' => 'boolean',
        'dispensed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function pharmacist()
    {
        return $this->belongsTo(User::class, 'pharmacist_id');
    }

    public function items()
    {
        return $this->hasMany(PrescriptionItem::class);
    }

    public function approve() {
        if (request()->user()->hasRole('pharmacist')) $this->pharmacist_id = auth()->id();
        $this->status = 'approved';
        $this->save();
    }

    public function reject() {
        if (request()->user()->hasRole('pharmacist')) $this->pharmacist_id = auth()->id();
        $this->status = 'rejected';
        $this->save();
    }

    public function cancel() {
        if (request()->user()->hasRole('pharmacist')) $this->pharmacist_id = auth()->id();
        $this->status = 'cancelled';
        $this->save();
    }

    public function dispenseRecords()
    {
        return $this->hasMany(DispenseRecord::class);
    }

    public function dispensedItems()
    {
        return $this->hasManyThrough(DispensedItem::class, DispenseRecord::class);
    }

    public function getLastDispensedAtAttribute()
    {
        return $this->dispenseRecords()->latest('dispensed_at')->first()->dispensed_at ?? null;
    }

    public function getIsFullyDispensedAttribute()
    {
        return $this->status === 'dispensed';
    }
}
