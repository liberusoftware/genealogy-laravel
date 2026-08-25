<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Relationships\Filament\Resources\RelationshipResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Relationships\Actions\DeleteRelationship;
use Liberu\Genealogy\Relationships\Actions\UpdateRelationship as UpdateRelationshipAction;
use Liberu\Genealogy\Relationships\Filament\Resources\RelationshipResource;

final class EditRelationship extends EditRecord
{
    protected static string $resource = RelationshipResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(UpdateRelationshipAction::class)->execute($record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()->action(fn (Model $record): mixed => app(DeleteRelationship::class)->execute($record))];
    }
}
