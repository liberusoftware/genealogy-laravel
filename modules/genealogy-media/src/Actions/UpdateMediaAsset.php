<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Media\Actions;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\Media\Events\MediaAssetUpdated;
use Liberu\Genealogy\Media\Models\MediaAsset;

final class UpdateMediaAsset
{
    /** @param array<string, mixed> $attributes */
    public function execute(MediaAsset $asset, array $attributes): MediaAsset
    {
        if ((string) $asset->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The media asset must belong to the active team.');
        }
        $values = Arr::only($attributes, ['kind', 'name', 'storage_disk', 'storage_path', 'mime_type', 'byte_size', 'checksum', 'captured_at', 'captured_place_id', 'transcription', 'transcription_status', 'transcription_language', 'rights_holder', 'rights_status', 'license_url', 'rights_expires_at', 'is_public', 'preservation_metadata', 'status', 'metadata']);
        (new CreateMediaAsset())->validate(array_merge($asset->toArray(), $values));
        if (array_key_exists('name', $values)) {
            $values['name'] = trim((string) $values['name']);
        }
        $asset->getConnection()->transaction(function () use ($asset, $values): void {
            $asset->update($values);
        });

        $asset = $asset->refresh();
        if (app()->bound('events')) {
            event(new MediaAssetUpdated($asset));
        }

        return $asset;
    }
}
