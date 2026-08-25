<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence;

use InvalidArgumentException;

final readonly class Confidence
{
    private function __construct(public int $value) {}

    public static function from(int $value): self
    {
        if ($value < 0 || $value > 100) {
            throw new InvalidArgumentException('Evidence confidence must be between 0 and 100.');
        }

        return new self($value);
    }

    public function isHigh(): bool
    {
        return $this->value >= 75;
    }
}
