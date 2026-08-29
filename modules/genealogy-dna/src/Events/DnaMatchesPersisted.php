<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Events;

use Liberu\Genealogy\Dna\Models\DnaKit;

final class DnaMatchesPersisted
{
    /** @param list<string> $matchIds @param list<string> $newMatchIds */
    public function __construct(
        public readonly DnaKit $kit,
        public readonly DnaKit $otherKit,
        public readonly array $matchIds,
        public readonly array $newMatchIds,
    ) {}
}
