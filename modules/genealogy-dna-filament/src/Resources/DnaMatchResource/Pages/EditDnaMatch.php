<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Filament\Resources\DnaMatchResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Dna\Actions\DeleteDnaMatch;
use Liberu\Genealogy\Dna\Actions\UpdateDnaMatch as UpdateDnaMatchAction;
use Liberu\Genealogy\Dna\Filament\Resources\DnaMatchResource;

final class EditDnaMatch extends EditRecord
{
    protected static string $resource = DnaMatchResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(UpdateDnaMatchAction::class)->execute($record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()->action(fn (Model $record): mixed => app(DeleteDnaMatch::class)->execute($record))];
    }
}
