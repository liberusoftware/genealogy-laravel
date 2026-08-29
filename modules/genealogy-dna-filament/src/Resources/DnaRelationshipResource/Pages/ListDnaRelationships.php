<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Filament\Resources\DnaRelationshipResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Liberu\Genealogy\Dna\Filament\Resources\DnaRelationshipResource;

final class ListDnaRelationships extends ListRecords
{
    protected static string $resource = DnaRelationshipResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
