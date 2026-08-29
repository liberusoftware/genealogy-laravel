<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Filament\Resources\DnaProviderResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Dna\Actions\DeleteDnaProvider;
use Liberu\Genealogy\Dna\Actions\UpdateDnaProvider as UpdateDnaProviderAction;
use Liberu\Genealogy\Dna\Filament\Resources\DnaProviderResource;

final class EditDnaProvider extends EditRecord
{
    protected static string $resource = DnaProviderResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(UpdateDnaProviderAction::class)->execute($record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()->action(fn (Model $record): mixed => app(DeleteDnaProvider::class)->execute($record))];
    }
}
