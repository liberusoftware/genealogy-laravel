<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Places\Filament\Resources\PlaceResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Places\Actions\CreatePlace as CreatePlaceAction;
use Liberu\Genealogy\Places\Filament\Resources\PlaceResource;

final class CreatePlace extends CreateRecord
{
    protected static string $resource = PlaceResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(CreatePlaceAction::class)->execute($data);
    }
}
