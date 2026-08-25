<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Places\Filament\Resources\PlaceResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Places\Actions\DeletePlace;
use Liberu\Genealogy\Places\Actions\UpdatePlace as UpdatePlaceAction;
use Liberu\Genealogy\Places\Filament\Resources\PlaceResource;

final class EditPlace extends EditRecord
{
    protected static string $resource = PlaceResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(UpdatePlaceAction::class)->execute($record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()->action(fn (Model $record): mixed => app(DeletePlace::class)->execute($record))];
    }
}
