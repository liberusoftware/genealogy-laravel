<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Filament\Resources\DnaRelationshipResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Liberu\Genealogy\Dna\Filament\Resources\DnaRelationshipResource;

final class EditDnaRelationship extends EditRecord
{
    protected static string $resource = DnaRelationshipResource::class;
}
