<?php

declare(strict_types=1);

namespace Liberu\Genealogy\GenealogyCore;

use LogicException;

/**
 * Request/job-local team context used by tenant-owned genealogy aggregates.
 *
 * The application is responsible for resolving membership before setting this
 * context. It must be cleared when the request or queued job ends.
 */
final class TeamContext
{
    private ?string $teamId = null;

    public function set(string|int $teamId): void
    {
        if ((string) $teamId === '') {
            throw new LogicException('A team context requires a non-empty team identifier.');
        }

        $this->teamId = (string) $teamId;
    }

    public function current(): ?string
    {
        return $this->teamId;
    }

    public function require(): string
    {
        if ($this->teamId === null) {
            throw new LogicException('A team context is required for this operation.');
        }

        return $this->teamId;
    }

    public function clear(): void
    {
        $this->teamId = null;
    }

    /**
     * Execute work within a team context and restore the previous context.
     *
     * This is safe for queued jobs and other long-lived runtimes where this
     * container binding can outlive one unit of work.
     *
     * @template TValue
     *
     * @param  callable(): TValue  $callback
     * @return TValue
     */
    public function run(string|int $teamId, callable $callback): mixed
    {
        $previous = $this->teamId;
        $this->set($teamId);

        try {
            return $callback();
        } finally {
            $this->teamId = $previous;
        }
    }
}
