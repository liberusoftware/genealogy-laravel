<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Media\Actions;

use Illuminate\Support\Facades\Storage;
use Liberu\Genealogy\Media\Contracts\FaceRecognitionProvider;
use Liberu\Genealogy\Media\Models\MediaAsset;
use Liberu\Genealogy\Media\Models\MediaFaceTag;

final class AnalyzeMediaFaces
{
    public function __construct(private readonly ?FaceRecognitionProvider $provider = null) {}

    /** @return array{available: bool, success: bool, faces_detected: int, tags_created: int, error: ?string} */
    public function execute(MediaAsset $asset): array
    {
        if ($this->provider === null || ! $this->provider->isAvailable()) {
            return ['available' => false, 'success' => false, 'faces_detected' => 0, 'tags_created' => 0, 'error' => 'Facial recognition is not configured.'];
        }

        $disk = $asset->storage_disk ?: config('filesystems.default');
        $path = (string) $asset->storage_path;
        if ($path === '' || ! method_exists(Storage::disk($disk), 'path')) {
            return ['available' => true, 'success' => false, 'faces_detected' => 0, 'tags_created' => 0, 'error' => 'The media asset has no local file path.'];
        }

        $absolutePath = Storage::disk($disk)->path($path);
        if (! is_file($absolutePath) || ! is_readable($absolutePath)) {
            return ['available' => true, 'success' => false, 'faces_detected' => 0, 'tags_created' => 0, 'error' => 'The media file could not be read.'];
        }

        $faces = $this->provider->detect($absolutePath);
        $tagsCreated = 0;
        foreach ($faces as $face) {
            if (! isset($face['bounding_box']) || ! is_array($face['bounding_box'])) {
                continue;
            }

            MediaFaceTag::query()->create([
                'team_id' => $asset->team_id,
                'media_asset_id' => $asset->getKey(),
                'confidence' => $face['confidence'],
                'bounding_box' => $face['bounding_box'],
                'status' => 'pending',
                'metadata' => ['provider_face_id' => $face['face_id']],
            ]);
            $tagsCreated++;
        }

        return ['available' => true, 'success' => true, 'faces_detected' => count($faces), 'tags_created' => $tagsCreated, 'error' => null];
    }
}
