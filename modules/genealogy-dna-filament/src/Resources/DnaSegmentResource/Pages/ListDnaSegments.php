<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Filament\Resources\DnaSegmentResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Liberu\Genealogy\Dna\Filament\Resources\DnaSegmentResource;

final class ListDnaSegments extends ListRecords
{
    protected static string $resource = DnaSegmentResource::class;
}
