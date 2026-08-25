<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Filament\Resources;

use Filament\Resources\Pages\PageRegistration;
use Liberu\Genealogy\Evidence\Filament\Resources\CitationResource\Pages\CreateCitation;
use Liberu\Genealogy\Evidence\Filament\Resources\CitationResource\Pages\EditCitation;
use Liberu\Genealogy\Evidence\Filament\Resources\CitationResource\Pages\ListCitations;
use Liberu\Genealogy\Evidence\Filament\Resources\CitationResource\RelationManagers\PersonLinksRelationManager;
use Liberu\Genealogy\Evidence\Models\Citation;

final class CitationResource extends EvidenceEntityResource
{
    protected static ?string $model = Citation::class;

    protected static ?string $navigationLabel = 'Citations';

    /** @return array<string, PageRegistration> */
    public static function getPages(): array
    {
        return ['index' => ListCitations::route('/'), 'create' => CreateCitation::route('/create'), 'edit' => EditCitation::route('/{record}/edit')];
    }

    public static function getRelations(): array
    {
        return [PersonLinksRelationManager::class];
    }
}
