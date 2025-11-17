<?php

namespace App\Services;

/**
 * Value object describing a projection replay run.
 */
class ProjectionReplayOptions
{
    public function __construct(
        public ?string $projection = null,
        public ?string $aggregateType = null,
        public ?int $fromId = null,
        public ?int $toId = null,
        public bool $dryRun = false,
        public int $batchSize = 100,
    ) {
    }
}

