<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Media\Filament\Resources\MediaAssetResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Media\Actions\DeleteMediaAsset;
use Liberu\Genealogy\Media\Actions\UpdateMediaAsset;
use Liberu\Genealogy\Media\Filament\Resources\MediaAssetResource;

final class EditMediaAsset extends EditRecord
{
    protected static string $resource = MediaAssetResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(UpdateMediaAsset::class)->execute($record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()->action(fn (Model $record): mixed => app(DeleteMediaAsset::class)->execute($record))];
    }
}
