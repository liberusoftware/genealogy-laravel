<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Relationships\Filament\Resources\RelationshipResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Liberu\Genealogy\Relationships\Filament\Resources\RelationshipResource;

final class ListRelationships extends ListRecords
{
    protected static string $resource = RelationshipResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
