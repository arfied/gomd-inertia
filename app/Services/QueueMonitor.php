<?php

namespace App\Services;

use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class QueueMonitor
{
    public function recordProcessed(JobProcessed $event): void
    {
        $jobName = $event->job->resolveName();

        Cache::increment('metrics.queue.jobs_processed');
        Cache::increment('metrics.queue.jobs_processed.by_name.' . $jobName);

        Log::info('Queue job processed', [
            'connection' => $event->connectionName,
            'queue' => $event->job->getQueue(),
            'job' => $jobName,
        ]);
    }

    public function recordFailed(JobFailed $event): void
    {
        $jobName = $event->job->resolveName();

        Cache::increment('metrics.queue.jobs_failed');
        Cache::increment('metrics.queue.jobs_failed.by_name.' . $jobName);

        Log::error('Queue job failed', [
            'connection' => $event->connectionName,
            'queue' => $event->job->getQueue(),
            'job' => $jobName,
            'exception' => $event->exception->getMessage(),
        ]);
    }
}

