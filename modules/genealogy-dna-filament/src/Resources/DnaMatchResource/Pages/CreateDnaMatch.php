<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Filament\Resources\DnaMatchResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Liberu\Genealogy\Dna\Filament\Resources\DnaMatchResource;

final class CreateDnaMatch extends CreateRecord
{
    protected static string $resource = DnaMatchResource::class;
}
