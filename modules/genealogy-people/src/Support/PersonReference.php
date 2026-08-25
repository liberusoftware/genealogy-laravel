<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People\Support;

use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\Contracts\PersonReferenceResolver;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\People\Models\Person;

final class PersonReference implements PersonReferenceResolver
{
    public function __construct(private readonly TeamContext $teamContext) {}

    public function require(mixed $personId): string
    {
        $id = trim((string) $personId);
        if ($id === '' || ! Person::query()->whereKey($id)->exists()) {
            throw new InvalidArgumentException('The person must belong to the active team.');
        }

        $this->teamContext->require();

        return $id;
    }

    public function existsForTeam(mixed $personId, string $teamId): bool
    {
        return $this->teamContext->run($teamId, fn (): bool => Person::query()->whereKey($personId)->exists());
    }
}
