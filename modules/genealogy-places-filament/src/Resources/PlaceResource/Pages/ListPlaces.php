<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Places\Filament\Resources\PlaceResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Liberu\Genealogy\Places\Filament\Resources\PlaceResource;

final class ListPlaces extends ListRecords
{
    protected static string $resource = PlaceResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
