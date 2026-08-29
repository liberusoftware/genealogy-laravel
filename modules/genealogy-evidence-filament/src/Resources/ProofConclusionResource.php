<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Filament\Resources;

use Filament\Resources\Pages\PageRegistration;
use Liberu\Genealogy\Evidence\Filament\Resources\ProofConclusionResource\Pages\CreateProofConclusion;
use Liberu\Genealogy\Evidence\Filament\Resources\ProofConclusionResource\Pages\EditProofConclusion;
use Liberu\Genealogy\Evidence\Filament\Resources\ProofConclusionResource\Pages\ListProofConclusions;
use Liberu\Genealogy\Evidence\Models\ProofConclusion;

final class ProofConclusionResource extends EvidenceEntityResource
{
    protected static ?string $model = ProofConclusion::class;

    protected static ?string $navigationLabel = 'Proof conclusions';

    /** @return array<string, PageRegistration> */
    public static function getPages(): array
    {
        return ['index' => ListProofConclusions::route('/'), 'create' => CreateProofConclusion::route('/create'), 'edit' => EditProofConclusion::route('/{record}/edit')];
    }
}
