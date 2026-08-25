<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Filament\Resources\ProofConclusionResource\Pages;

use Liberu\Genealogy\Evidence\Filament\Resources\Pages\CreateEvidenceEntityRecord;
use Liberu\Genealogy\Evidence\Filament\Resources\ProofConclusionResource;

final class CreateProofConclusion extends CreateEvidenceEntityRecord
{
    protected static string $resource = ProofConclusionResource::class;
}
