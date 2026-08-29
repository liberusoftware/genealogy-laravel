<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Filament\Resources\DnaRelationshipResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Dna\Actions\DeleteDnaRelationship;
use Liberu\Genealogy\Dna\Actions\UpdateDnaRelationship;
use Liberu\Genealogy\Dna\Filament\Resources\DnaRelationshipResource;

final class EditDnaRelationship extends EditRecord
{
    protected static string $resource = DnaRelationshipResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(UpdateDnaRelationship::class)->execute($record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()->action(fn (Model $record): mixed => app(DeleteDnaRelationship::class)->execute($record))];
    }
}
