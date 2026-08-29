<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Filament\Resources\DnaNoteResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Liberu\Genealogy\Dna\Filament\Resources\DnaNoteResource;

final class ListDnaNotes extends ListRecords
{
    protected static string $resource = DnaNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
