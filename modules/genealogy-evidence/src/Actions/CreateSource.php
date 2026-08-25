<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Actions;

use Liberu\Genealogy\Evidence\Models\Source;

final class CreateSource
{
    public function __construct(private readonly CreateEvidenceEntity $create) {}

    public function execute(array $attributes): Source
    {
        return $this->create->execute(Source::class, $attributes);
    }
}
