<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Filament\Resources\DnaRelationshipResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Dna\Actions\CreateDnaRelationship as CreateRelationship;
use Liberu\Genealogy\Dna\Filament\Resources\DnaRelationshipResource;

final class CreateDnaRelationship extends CreateRecord
{
    protected static string $resource = DnaRelationshipResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(CreateRelationship::class)->execute($data);
    }
}
