<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People\Filament\Resources\PersonResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Liberu\Genealogy\People\Filament\Resources\PersonResource;

final class CreatePerson extends CreateRecord
{
    protected static string $resource = PersonResource::class;
}
