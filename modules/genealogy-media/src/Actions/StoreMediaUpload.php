<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Media\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Liberu\Genealogy\Media\Models\MediaAsset;
use Throwable;

final class StoreMediaUpload
{
    /** @param array<string, mixed> $attributes */
    public function execute(UploadedFile $file, array $attributes = []): MediaAsset
    {
        $disk = (string) ($attributes['storage_disk'] ?? config('filesystems.default', 'local'));
        $directory = trim((string) ($attributes['storage_directory'] ?? 'genealogy-media'), '/');
        $filesystem = Storage::disk($disk);
        $path = $filesystem->putFile($directory, $file);

        if ($path === false) {
            throw new \RuntimeException('The media file could not be stored.');
        }

        try {
            return app(CreateMediaAsset::class)->execute(array_merge($attributes, [
                'name' => $attributes['name'] ?? $file->getClientOriginalName(),
                'kind' => $attributes['kind'] ?? $this->kind($file),
                'storage_disk' => $disk,
                'storage_path' => $path,
                'mime_type' => $file->getMimeType(),
                'byte_size' => $file->getSize(),
                'checksum' => hash_file('sha256', $file->getRealPath()),
                'preservation_metadata' => array_merge((array) ($attributes['preservation_metadata'] ?? []), [
                    'original_name' => $file->getClientOriginalName(),
                    'uploaded_at' => now()->toISOString(),
                    'storage_directory' => $directory,
                ]),
            ]));
        } catch (Throwable $exception) {
            $filesystem->delete($path);
            throw $exception;
        }
    }

    private function kind(UploadedFile $file): string
    {
        return match (true) {
            Str::startsWith((string) $file->getMimeType(), 'image/') => 'photograph',
            Str::startsWith((string) $file->getMimeType(), 'audio/') => 'audio',
            Str::startsWith((string) $file->getMimeType(), 'video/') => 'video',
            default => 'document',
        };
    }
}
