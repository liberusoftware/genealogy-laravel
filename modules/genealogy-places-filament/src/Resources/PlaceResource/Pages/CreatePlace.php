<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Places\Filament\Resources\PlaceResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Liberu\Genealogy\Places\Filament\Resources\PlaceResource;

final class CreatePlace extends CreateRecord
{
    protected static string $resource = PlaceResource::class;
}
