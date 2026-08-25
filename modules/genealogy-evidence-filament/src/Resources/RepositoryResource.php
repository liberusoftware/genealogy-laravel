<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Filament\Resources;

use Filament\Resources\Pages\PageRegistration;
use Liberu\Genealogy\Evidence\Filament\Resources\RepositoryResource\Pages\CreateRepository;
use Liberu\Genealogy\Evidence\Filament\Resources\RepositoryResource\Pages\EditRepository;
use Liberu\Genealogy\Evidence\Filament\Resources\RepositoryResource\Pages\ListRepositories;
use Liberu\Genealogy\Evidence\Models\Repository;

final class RepositoryResource extends EvidenceEntityResource
{
    protected static ?string $model = Repository::class;

    protected static ?string $navigationLabel = 'Repositories';

    /** @return array<string, PageRegistration> */
    public static function getPages(): array
    {
        return ['index' => ListRepositories::route('/'), 'create' => CreateRepository::route('/create'), 'edit' => EditRepository::route('/{record}/edit')];
    }
}
