<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Filament\Resources\DnaMatchResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Liberu\Genealogy\Dna\Filament\Resources\DnaMatchResource;

final class ListDnaMatches extends ListRecords
{
    protected static string $resource = DnaMatchResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
