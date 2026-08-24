<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Filament\Resources\DnaKitResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Liberu\Genealogy\Dna\Filament\Resources\DnaKitResource;

final class ListDnaKits extends ListRecords
{
    protected static string $resource = DnaKitResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
