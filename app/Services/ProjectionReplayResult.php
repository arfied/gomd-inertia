<?php

namespace App\Services;

/**
 * Result statistics for a projection replay run.
 */
class ProjectionReplayResult
{
    public int $batches = 0;

    public int $eventsProcessed = 0;

    public int $eventsDispatched = 0;
}

