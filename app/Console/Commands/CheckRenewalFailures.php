<?php

namespace App\Console\Commands;

use App\Domain\Subscription\SubscriptionRenewalSaga;
use App\Models\Subscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckRenewalFailures extends Command
{
    protected $signature = 'renewal:check-failures {--days=7 : Number of days to check}';
    protected $description = 'Check for subscription renewal failures in the past N days';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $since = now()->subDays($days);

        $this->info("Checking for renewal failures since {$since->toDateTimeString()}...\n");

        // Query the event store for failed renewal sagas
        $failedSagas = DB::table('event_store')
            ->where('event_type', 'SubscriptionRenewalSagaFailed')
            ->where('created_at', '>=', $since)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($failedSagas->isEmpty()) {
            $this->info("No renewal failures found in the past {$days} days.");
            return 0;
        }

        $this->info("Found {$failedSagas->count()} renewal failures:\n");

        $headers = ['Saga UUID', 'Reason', 'Created At'];
        $rows = [];

        foreach ($failedSagas as $saga) {
            $payload = json_decode($saga->payload, true);
            $rows[] = [
                substr($saga->aggregate_uuid, 0, 8) . '...',
                $payload['reason'] ?? 'Unknown',
                $saga->created_at,
            ];
        }

        $this->table($headers, $rows);

        // Summary statistics
        $this->info("\n--- Summary ---");
        $this->info("Total failures: {$failedSagas->count()}");
        $this->info("Period: {$since->toDateString()} to " . now()->toDateString());

        return 0;
    }
}

