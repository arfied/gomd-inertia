<?php

namespace App\Application\Commands;

use App\Domain\Shared\Commands\Command;
use InvalidArgumentException;

/**
 * Minimal in-memory command bus.
 *
 * This is scaffolding only; handlers can be registered via
 * a service provider or manual wiring.
 */
class CommandBus
{
    /** @var array<class-string<Command>, CommandHandler> */
    private array $handlers = [];

    /**
     * Register a handler for a command class.
     *
     * @param class-string<Command> $commandClass
     */
    public function register(string $commandClass, CommandHandler $handler): void
    {
        $this->handlers[$commandClass] = $handler;
    }

    /**
     * Dispatch a command to its handler.
     */
    public function dispatch(Command $command): void
    {
        $commandClass = $command::class;

        if (! isset($this->handlers[$commandClass])) {
            throw new InvalidArgumentException("No handler registered for command {$commandClass}");
        }

        $this->handlers[$commandClass]->handle($command);
    }
}

