<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Filament\Resources\DnaGroupResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Liberu\Genealogy\Dna\Filament\Resources\DnaGroupResource;

final class CreateDnaGroup extends CreateRecord
{
    protected static string $resource = DnaGroupResource::class;
}
