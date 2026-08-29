<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Filament\Resources;

use Filament\Resources\Pages\PageRegistration;
use Liberu\Genealogy\Evidence\Filament\Resources\SourceResource\Pages\CreateSource;
use Liberu\Genealogy\Evidence\Filament\Resources\SourceResource\Pages\EditSource;
use Liberu\Genealogy\Evidence\Filament\Resources\SourceResource\Pages\ListSources;
use Liberu\Genealogy\Evidence\Models\Source;

final class SourceResource extends EvidenceEntityResource
{
    protected static ?string $model = Source::class;

    protected static ?string $navigationLabel = 'Sources';

    /** @return array<string, PageRegistration> */
    public static function getPages(): array
    {
        return ['index' => ListSources::route('/'), 'create' => CreateSource::route('/create'), 'edit' => EditSource::route('/{record}/edit')];
    }
}
