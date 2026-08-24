<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Media\Filament\Resources\MediaAssetResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Liberu\Genealogy\Media\Filament\Resources\MediaAssetResource;

final class CreateMediaAsset extends CreateRecord
{
    protected static string $resource = MediaAssetResource::class;
}
