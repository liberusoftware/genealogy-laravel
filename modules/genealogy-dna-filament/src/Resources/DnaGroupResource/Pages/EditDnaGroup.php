<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Filament\Resources\DnaGroupResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Dna\Actions\DeleteDnaGroup;
use Liberu\Genealogy\Dna\Actions\UpdateDnaGroup as UpdateDnaGroupAction;
use Liberu\Genealogy\Dna\Filament\Resources\DnaGroupResource;

final class EditDnaGroup extends EditRecord
{
    protected static string $resource = DnaGroupResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(UpdateDnaGroupAction::class)->execute($record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()->action(fn (Model $record): mixed => app(DeleteDnaGroup::class)->execute($record))];
    }
}
