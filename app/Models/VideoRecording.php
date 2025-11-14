<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoRecording extends Model
{
    protected $fillable = [
        'user_id',
        'user_service_id',
        'filename',
        'path',
        'format',
        'duration'
    ];
}
