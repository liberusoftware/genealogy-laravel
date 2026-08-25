<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Filament\Resources\ProofConclusionResource\Pages;

use Liberu\Genealogy\Evidence\Filament\Resources\Pages\ListEvidenceEntities;
use Liberu\Genealogy\Evidence\Filament\Resources\ProofConclusionResource;

final class ListProofConclusions extends ListEvidenceEntities
{
    protected static string $resource = ProofConclusionResource::class;
}
