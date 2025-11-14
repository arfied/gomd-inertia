<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $fillable = [
        'medication_id', 'quantity', 'expiry_date', 'batch_number',
        'reorder_point', 'max_stock_level'
    ];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    public function medication()
    {
        return $this->belongsTo(Medication::class);
    }

    public function isLowStock()
    {
        return $this->quantity <= $this->reorder_point;
    }
}
