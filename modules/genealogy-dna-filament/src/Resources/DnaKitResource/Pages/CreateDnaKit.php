<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Filament\Resources\DnaKitResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Liberu\Genealogy\Dna\Filament\Resources\DnaKitResource;

final class CreateDnaKit extends CreateRecord
{
    protected static string $resource = DnaKitResource::class;
}
