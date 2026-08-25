<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Discovery\Contracts;

/** Optional adapter for external record services; provider SDKs stay outside this module. */
interface ExternalRecordProvider
{
    public function key(): string;

    public function isAvailable(): bool;

    /** @return list<array<string, mixed>> */
    public function search(array $person): array;
}
