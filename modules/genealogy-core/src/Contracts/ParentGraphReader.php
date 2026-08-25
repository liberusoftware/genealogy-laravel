<?php

declare(strict_types=1);

namespace Liberu\Genealogy\GenealogyCore\Contracts;

interface ParentGraphReader
{
    /** @param list<string> $personIds @return list<string> */
    public function relatedPeople(string $teamId, array $personIds, bool $ancestors): array;
}
