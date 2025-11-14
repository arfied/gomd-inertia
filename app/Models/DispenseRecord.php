<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DispenseRecord extends Model
{
    protected $fillable = ['prescription_id', 'dispensed_by', 'dispensed_at'];

    public function items()
    {
        return $this->hasMany(DispensedItem::class);
    }
}
