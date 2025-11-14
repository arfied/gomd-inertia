<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Commission;
use App\Models\Referral;

class AgentCustomReport extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'agent_id',
        'name',
        'type',
        'filters',
        'columns',
        'sorting',
        'is_scheduled',
        'schedule_frequency',
        'schedule_time',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'filters' => 'array',
        'columns' => 'array',
        'sorting' => 'array',
        'is_scheduled' => 'boolean',
        'schedule_time' => 'datetime',
    ];

    /**
     * Get the agent that owns the custom report.
     */
    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    /**
     * Get the exports for this report.
     */
    public function exports()
    {
        return $this->hasMany(AgentReportExport::class, 'report_id');
    }

    /**
     * Get the latest export for this report.
     */
    public function latestExport()
    {
        return $this->hasOne(AgentReportExport::class, 'report_id')
                    ->latest('generated_at');
    }

    /**
     * Generate the report data.
     */
    public function generateData()
    {
        $query = $this->buildQuery();

        // Apply sorting
        if (!empty($this->sorting)) {
            foreach ($this->sorting as $sort) {
                $direction = $sort['direction'] ?? 'asc';
                $query->orderBy($sort['column'], $direction);
            }
        }

        return $query->get();
    }

    /**
     * Build the query for the report.
     */
    protected function buildQuery()
    {
        switch ($this->type) {
            case 'commission':
                return $this->buildCommissionQuery();
            case 'referral':
                return $this->buildReferralQuery();
            case 'performance':
                return $this->buildPerformanceQuery();
            default:
                return Commission::where('agent_id', $this->agent_id);
        }
    }

    /**
     * Build the commission query.
     */
    protected function buildCommissionQuery()
    {
        $query = Commission::where('agent_id', $this->agent_id);

        if (!empty($this->filters)) {
            if (isset($this->filters['date_from'])) {
                $query->where('created_at', '>=', $this->filters['date_from']);
            }

            if (isset($this->filters['date_to'])) {
                $query->where('created_at', '<=', $this->filters['date_to']);
            }

            if (isset($this->filters['status'])) {
                $query->where('status', $this->filters['status']);
            }

            if (isset($this->filters['source'])) {
                $query->where('source', $this->filters['source']);
            }

            if (isset($this->filters['amount_min'])) {
                $query->where('amount', '>=', $this->filters['amount_min']);
            }

            if (isset($this->filters['amount_max'])) {
                $query->where('amount', '<=', $this->filters['amount_max']);
            }
        }

        return $query;
    }

    /**
     * Build the referral query.
     */
    protected function buildReferralQuery()
    {
        $query = Referral::where('referring_agent_id', $this->agent_id);

        if (!empty($this->filters)) {
            if (isset($this->filters['date_from'])) {
                $query->where('created_at', '>=', $this->filters['date_from']);
            }

            if (isset($this->filters['date_to'])) {
                $query->where('created_at', '<=', $this->filters['date_to']);
            }

            if (isset($this->filters['status'])) {
                $query->where('status', $this->filters['status']);
            }

            if (isset($this->filters['source'])) {
                $query->where('source', $this->filters['source']);
            }
        }

        return $query;
    }

    /**
     * Build the performance query.
     */
    protected function buildPerformanceQuery()
    {
        // This is a more complex query that might join multiple tables
        // For simplicity, we'll just return a commission query for now
        return $this->buildCommissionQuery();
    }
}
