<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Label extends Model
{
    protected $fillable = ['prescription_item_id', 'instructions'];

    public function prescriptionItem()
    {
        return $this->belongsTo(PrescriptionItem::class);
    }
}
