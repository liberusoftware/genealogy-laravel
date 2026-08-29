<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Filament\Resources;

use Filament\Resources\Pages\PageRegistration;
use Liberu\Genealogy\Evidence\Filament\Resources\ExtractResource\Pages\CreateExtract;
use Liberu\Genealogy\Evidence\Filament\Resources\ExtractResource\Pages\EditExtract;
use Liberu\Genealogy\Evidence\Filament\Resources\ExtractResource\Pages\ListExtracts;
use Liberu\Genealogy\Evidence\Models\Extract;

final class ExtractResource extends EvidenceEntityResource
{
    protected static ?string $model = Extract::class;

    protected static ?string $navigationLabel = 'Extracts';

    /** @return array<string, PageRegistration> */
    public static function getPages(): array
    {
        return ['index' => ListExtracts::route('/'), 'create' => CreateExtract::route('/create'), 'edit' => EditExtract::route('/{record}/edit')];
    }
}
