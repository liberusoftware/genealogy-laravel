<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Relationships\Filament\Resources\RelationshipResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Liberu\Genealogy\Relationships\Filament\Resources\RelationshipResource;

final class EditRelationship extends EditRecord
{
    protected static string $resource = RelationshipResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
