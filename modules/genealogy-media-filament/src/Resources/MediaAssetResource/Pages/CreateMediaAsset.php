<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Media\Filament\Resources\MediaAssetResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Liberu\Genealogy\Media\Actions\CreateMediaAsset as CreateMediaAssetAction;
use Liberu\Genealogy\Media\Actions\StoreMediaUpload;
use Liberu\Genealogy\Media\Filament\Resources\MediaAssetResource;

final class CreateMediaAsset extends CreateRecord
{
    protected static string $resource = MediaAssetResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        if (($data['upload'] ?? null) instanceof UploadedFile) {
            $upload = $data['upload'];
            unset($data['upload']);

            return app(StoreMediaUpload::class)->execute($upload, $data);
        }

        return app(CreateMediaAssetAction::class)->execute($data);
    }
}
