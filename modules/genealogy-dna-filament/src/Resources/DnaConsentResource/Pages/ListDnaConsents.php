<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Filament\Resources\DnaConsentResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Liberu\Genealogy\Dna\Filament\Resources\DnaConsentResource;

final class ListDnaConsents extends ListRecords
{
    protected static string $resource = DnaConsentResource::class;
}
