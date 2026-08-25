<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Actions;

use Liberu\Genealogy\Evidence\Models\Citation;

final class CreateCitation
{
    public function __construct(private readonly CreateEvidenceEntity $create) {}

    public function execute(array $attributes): Citation
    {
        return $this->create->execute(Citation::class, $attributes);
    }
}
