<?php

namespace App\Application\Queries;

use App\Domain\Shared\Queries\Query;
use InvalidArgumentException;

/**
 * Minimal in-memory query bus.
 */
class QueryBus
{
    /** @var array<class-string<Query>, QueryHandler> */
    private array $handlers = [];

    /**
     * Register a handler for a query class.
     *
     * @param class-string<Query> $queryClass
     */
    public function register(string $queryClass, QueryHandler $handler): void
    {
        $this->handlers[$queryClass] = $handler;
    }

    /**
     * Dispatch a query to its handler and return the result.
     */
    public function ask(Query $query): mixed
    {
        $queryClass = $query::class;

        if (! isset($this->handlers[$queryClass])) {
            throw new InvalidArgumentException("No handler registered for query {$queryClass}");
        }

        return $this->handlers[$queryClass]->handle($query);
    }
}

