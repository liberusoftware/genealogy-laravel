<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Media\Filament\Resources\MediaAssetResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Liberu\Genealogy\Media\Filament\Resources\MediaAssetResource;

final class EditMediaAsset extends EditRecord
{
    protected static string $resource = MediaAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
