<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Filament\Resources\ProofConclusionResource\Pages;

use Liberu\Genealogy\Evidence\Filament\Resources\Pages\EditEvidenceEntityRecord;
use Liberu\Genealogy\Evidence\Filament\Resources\ProofConclusionResource;

final class EditProofConclusion extends EditEvidenceEntityRecord
{
    protected static string $resource = ProofConclusionResource::class;
}
