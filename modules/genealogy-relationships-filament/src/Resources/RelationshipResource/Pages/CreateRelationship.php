<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Relationships\Filament\Resources\RelationshipResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Liberu\Genealogy\Relationships\Filament\Resources\RelationshipResource;

final class CreateRelationship extends CreateRecord
{
    protected static string $resource = RelationshipResource::class;
}
