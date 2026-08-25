<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Filament\Resources\DnaKitResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Dna\Actions\DeleteDnaKit;
use Liberu\Genealogy\Dna\Actions\UpdateDnaKit as UpdateDnaKitAction;
use Liberu\Genealogy\Dna\Filament\Resources\DnaKitResource;

final class EditDnaKit extends EditRecord
{
    protected static string $resource = DnaKitResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(UpdateDnaKitAction::class)->execute($record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()->action(fn (Model $record): mixed => app(DeleteDnaKit::class)->execute($record))];
    }
}
