<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Media\Actions;

use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\Media\Contracts\TranscriptionProvider;
use Liberu\Genealogy\Media\Models\MediaAsset;

final class TranscribeMediaAsset
{
    public function __construct(private readonly ?TranscriptionProvider $provider = null) {}

    /** @return array{available: bool, success: bool, status: string, text: ?string, confidence: ?float, language: ?string, error: ?string} */
    public function execute(MediaAsset $asset): array
    {
        if ((string) $asset->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The media asset must belong to the active team.');
        }

        if ($this->provider === null || ! $this->provider->isAvailable()) {
            return ['available' => false, 'success' => false, 'status' => 'not_started', 'text' => null, 'confidence' => null, 'language' => null, 'error' => 'Transcription is not configured.'];
        }

        $disk = $asset->storage_disk ?: config('filesystems.default');
        $path = (string) $asset->storage_path;
        if ($path === '' || ! method_exists(Storage::disk($disk), 'path')) {
            return $this->failure('The media asset has no local file path.');
        }

        $absolutePath = Storage::disk($disk)->path($path);
        if (! is_file($absolutePath) || ! is_readable($absolutePath)) {
            return $this->failure('The media file could not be read.');
        }

        $asset->update(['transcription_status' => 'in_progress']);

        try {
            $result = $this->provider->transcribe($absolutePath);
            $metadata = array_merge($asset->metadata ?? [], ['transcription' => $result['metadata']]);
            if ($result['confidence'] !== null) {
                $metadata['transcription_confidence'] = $result['confidence'];
            }
            $asset->update(['transcription' => $result['text'], 'transcription_status' => 'completed', 'transcription_language' => $result['language'], 'metadata' => $metadata]);

            return ['available' => true, 'success' => true, 'status' => 'completed', 'text' => $result['text'], 'confidence' => $result['confidence'], 'language' => $result['language'], 'error' => null];
        } catch (\Throwable $exception) {
            $asset->update(['transcription_status' => 'failed', 'metadata' => array_merge($asset->metadata ?? [], ['transcription_error' => $exception->getMessage()])]);

            return $this->failure($exception->getMessage());
        }
    }

    /** @return array{available: bool, success: bool, status: string, text: ?string, confidence: ?float, language: ?string, error: string} */
    private function failure(string $error): array
    {
        return ['available' => true, 'success' => false, 'status' => 'failed', 'text' => null, 'confidence' => null, 'language' => null, 'error' => $error];
    }
}
