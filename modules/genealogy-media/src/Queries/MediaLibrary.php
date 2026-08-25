<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Media\Queries;

use Liberu\Genealogy\Media\Models\MediaAsset;

final class MediaLibrary
{
    /** @return list<array<string, mixed>> */
    public function execute(?string $kind = null, ?string $term = null, bool $publicOnly = false, int $limit = 50): array
    {
        $limit = min(max($limit, 1), 100);

        return MediaAsset::query()
            ->when($kind !== null, fn ($query) => $query->where('kind', $kind))
            ->when($term !== null && trim($term) !== '', fn ($query) => $query->where(function ($query) use ($term): void {
                $like = '%'.trim($term).'%';
                $query->where('name', 'like', $like)->orWhere('transcription', 'like', $like)->orWhere('rights_holder', 'like', $like);
            }))
            ->when($publicOnly, fn ($query) => $query->where('is_public', true))
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (MediaAsset $asset): array => [
                'id' => $asset->getKey(), 'kind' => $asset->kind, 'name' => $asset->name, 'mime_type' => $asset->mime_type,
                'byte_size' => $asset->byte_size, 'transcription_status' => $asset->transcription_status,
                'rights_status' => $asset->rights_status, 'is_public' => $asset->is_public, 'captured_at' => $asset->captured_at?->toISOString(),
            ])->values()->all();
    }
}
