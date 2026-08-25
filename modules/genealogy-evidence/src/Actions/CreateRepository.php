<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Actions;

use Liberu\Genealogy\Evidence\Models\Repository;

final class CreateRepository
{
    public function __construct(private readonly CreateEvidenceEntity $create) {}

    public function execute(array $attributes): Repository
    {
        return $this->create->execute(Repository::class, $attributes);
    }
}
