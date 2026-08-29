<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Filament\Resources\DnaProviderResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Liberu\Genealogy\Dna\Filament\Resources\DnaProviderResource;

final class ListDnaProviders extends ListRecords
{
    protected static string $resource = DnaProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
