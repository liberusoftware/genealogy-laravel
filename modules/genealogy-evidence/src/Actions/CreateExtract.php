<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Actions;

use Liberu\Genealogy\Evidence\Models\Extract;

final class CreateExtract
{
    public function __construct(private readonly CreateEvidenceEntity $create) {}

    public function execute(array $attributes): Extract
    {
        return $this->create->execute(Extract::class, $attributes);
    }
}
