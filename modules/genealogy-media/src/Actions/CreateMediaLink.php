<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Media\Actions;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use Liberu\Genealogy\Media\Events\MediaLinkCreated;
use Liberu\Genealogy\Media\Models\MediaAsset;
use Liberu\Genealogy\Media\Models\MediaLink;

final class CreateMediaLink
{
    public function execute(array $attributes): MediaLink
    {
        if (! MediaAsset::query()->whereKey($attributes['media_asset_id'] ?? null)->exists()) {
            throw new InvalidArgumentException('The linked media asset must belong to the active team.');
        }
        $link = MediaLink::query()->firstOrCreate(
            Arr::only($attributes, ['media_asset_id', 'linkable_type', 'linkable_id', 'role']),
            ['metadata' => $attributes['metadata'] ?? null],
        );
        if ($link->wasRecentlyCreated && app()->bound('events')) {
            event(new MediaLinkCreated($link));
        }

        return $link;
    }
}
