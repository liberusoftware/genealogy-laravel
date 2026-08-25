<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Filament\Resources;

use Liberu\Genealogy\Evidence\Models\ProofConclusion;

final class ProofConclusionResource extends EvidenceEntityResource
{
    protected static ?string $model = ProofConclusion::class;

    protected static ?string $navigationLabel = 'Proof conclusions';
}
