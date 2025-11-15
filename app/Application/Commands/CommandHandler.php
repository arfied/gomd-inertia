<?php

namespace App\Application\Commands;

use App\Domain\Shared\Commands\Command;

/**
 * A command handler executes a single command on the write model.
 */
interface CommandHandler
{
    /**
     * Handle the given command.
     */
    public function handle(Command $command): void;
}

