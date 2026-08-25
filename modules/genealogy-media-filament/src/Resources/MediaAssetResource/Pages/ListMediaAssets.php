<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Media\Filament\Resources\MediaAssetResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Liberu\Genealogy\Media\Filament\Resources\MediaAssetResource;

final class ListMediaAssets extends ListRecords
{
    protected static string $resource = MediaAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
