<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Relationships\Filament\Resources\RelationshipResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Relationships\Actions\CreateRelationship as CreateRelationshipAction;
use Liberu\Genealogy\Relationships\Filament\Resources\RelationshipResource;

final class CreateRelationship extends CreateRecord
{
    protected static string $resource = RelationshipResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(CreateRelationshipAction::class)->execute($data);
    }
}
