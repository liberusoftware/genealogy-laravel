<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Places\Filament\Resources\PlaceResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Liberu\Genealogy\Places\Filament\Resources\PlaceResource;

final class EditPlace extends EditRecord
{
    protected static string $resource = PlaceResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
