<?php

declare(strict_types=1);

namespace Liberu\Genealogy\GenealogyCore\Contracts;

interface PersonReferenceResolver
{
    public function existsForTeam(mixed $personId, string $teamId): bool;
}
