<?php

use App\Services\ProjectionReplayOptions;
use App\Services\ProjectionReplayService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('projections:replay {--projection=} {--aggregate-type=} {--from-id=} {--to-id=} {--dry-run}', function () {
    /** @var ProjectionReplayService $service */
    $service = app(ProjectionReplayService::class);

    $projection = $this->option('projection') ?: null;
    $aggregateType = $this->option('aggregate-type') ?: null;
    $fromId = $this->option('from-id') !== null ? (int) $this->option('from-id') : null;
    $toId = $this->option('to-id') !== null ? (int) $this->option('to-id') : null;
    $dryRun = (bool) $this->option('dry-run');

    if ($projection !== null && ! in_array($projection, $service->knownProjections(), true)) {
        $this->error('Unknown projection ['.$projection.']. Known projections: '.implode(', ', $service->knownProjections()));

        return self::FAILURE;
    }

    $this->info('Starting projection replay...');

    $result = $service->replay(
        new ProjectionReplayOptions(
            projection: $projection,
            aggregateType: $aggregateType,
            fromId: $fromId,
            toId: $toId,
            dryRun: $dryRun,
        ),
        output: function (string $line): void {
            $this->line($line);
        }
    );

    $this->info('Replay complete.');
    $this->line('Batches: '.$result->batches);
    $this->line('Events processed: '.$result->eventsProcessed);
    $this->line('Events dispatched: '.$result->eventsDispatched);
})->purpose('Replay events from the event store to rebuild projections');
