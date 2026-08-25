<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Actions;

use Liberu\Genealogy\Evidence\Models\ProofConclusion;

final class CreateProofConclusion
{
    public function __construct(private readonly CreateEvidenceEntity $create) {}

    public function execute(array $attributes): ProofConclusion
    {
        return $this->create->execute(ProofConclusion::class, $attributes);
    }
}
