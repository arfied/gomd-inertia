<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DispensedItem extends Model
{
    protected $fillable = ['dispense_record_id', 'prescription_item_id', 'quantity_dispensed', 'fully_dispensed'];

    public function prescriptionItem()
    {
        return $this->belongsTo(PrescriptionItem::class);
    }
}
