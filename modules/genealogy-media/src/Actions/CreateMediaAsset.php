<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Media\Actions;

use Illuminate\Support\Arr;
use Liberu\Genealogy\Media\Models\MediaAsset;

final class CreateMediaAsset
{
    public function execute(array $attributes): MediaAsset
    {
        return MediaAsset::query()->create(Arr::only($attributes, ['name', 'status', 'metadata']));
    }
}
