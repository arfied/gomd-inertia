<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfflineConversion extends Model
{
    protected $fillable = [
        'gclid',
        'conversion_name',
        'conversion_value',
        'conversion_currency',
        'user_id',
        'plan_id',
        'is_synced',
        'page',
        'event_type',
        'visitor_ip',
        'user_agent',
        'form_data',
        'source',
        'medium',
        'campaign',
    ];

    protected $casts = [
        'form_data' => 'array',
        'conversion_value' => 'decimal:2',
        'is_synced' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sync() {
        $this->is_synced = true;
        $this->save();
    }

    public function desync() {
        $this->is_synced = false;
        $this->save();
    }

    // Helper scopes for querying
    public function scopeWithGclid($query, $gclid)
    {
        return $query->where('gclid', $gclid);
    }

    public function scopeOfType($query, $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    public function scopeForLandingPage($query, $landingPage)
    {
        return $query->where('landing_page', $landingPage);
    }

    // Analytics methods
    public static function getConversionStats($landingPage = null, $dateRange = null)
    {
        $query = self::query();
        
        if ($landingPage) {
            $query->forLandingPage($landingPage);
        }

        if ($dateRange) {
            $query->whereBetween('created_at', $dateRange);
        }

        return [
            'total_visits' => $query->ofType('visit')->count(),
            'total_leads' => $query->ofType('form_submit')->count(),
            'total_purchases' => $query->ofType('payment')->count(),
            'total_revenue' => $query->ofType('payment')->sum('conversion_value'),
            'conversion_rate' => $query->ofType('form_submit')->count() / max($query->ofType('visit')->count(), 1) * 100,
            'purchase_rate' => $query->ofType('payment')->count() / max($query->ofType('visit')->count(), 1) * 100,
        ];
    }

    public static function getGclidJourney($gclid)
    {
        return self::withGclid($gclid)
            ->orderBy('created_at')
            ->get()
            ->map(function ($conversion) {
                return [
                    'event' => $conversion->event_type,
                    'value' => $conversion->conversion_value,
                    'timestamp' => $conversion->created_at,
                    'landing_page' => $conversion->landing_page,
                ];
            });
    }
}
