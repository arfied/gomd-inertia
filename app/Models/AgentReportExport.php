<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentReportExport extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'agent_id',
        'report_id',
        'report_type',
        'file_path',
        'format',
        'generated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'generated_at' => 'datetime',
    ];

    /**
     * Get the agent that owns the export.
     */
    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    /**
     * Get the custom report for this export.
     */
    public function report()
    {
        return $this->belongsTo(AgentCustomReport::class, 'report_id');
    }

    /**
     * Get the URL for downloading the export.
     */
    public function getDownloadUrlAttribute()
    {
        return url('/agent/reports/download/' . $this->id);
    }

    /**
     * Get the file size in a human-readable format.
     */
    public function getFileSizeAttribute()
    {
        if (!file_exists(storage_path('app/' . $this->file_path))) {
            return '0 KB';
        }

        $size = filesize(storage_path('app/' . $this->file_path));

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = $size > 0 ? floor(log($size, 1024)) : 0;

        return number_format($size / pow(1024, $power), 2) . ' ' . $units[$power];
    }

    /**
     * Delete the export file when the model is deleted.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($export) {
            if (file_exists(storage_path('app/' . $export->file_path))) {
                unlink(storage_path('app/' . $export->file_path));
            }
        });
    }
}
