<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Research;

final readonly class Capability
{
    /** @param list<string> $capabilities */
    public function __construct(
        public string $name,
        public string $displayName,
        public array $capabilities,
    ) {}

    public function supports(string $capability): bool
    {
        return in_array($capability, $this->capabilities, true);
    }
}
