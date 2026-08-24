<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Media\Actions;

use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Liberu\Genealogy\Media\Models\MediaAsset;

final class CreateMediaAsset
{
    public function execute(array $attributes): MediaAsset
    {
        $values = Arr::only($attributes, ['kind', 'name', 'storage_disk', 'storage_path', 'mime_type', 'byte_size', 'checksum', 'captured_at', 'captured_place_id', 'transcription', 'transcription_status', 'transcription_language', 'rights_holder', 'rights_status', 'license_url', 'rights_expires_at', 'is_public', 'preservation_metadata', 'status', 'metadata']);
        if (isset($values['kind']) && ! in_array($values['kind'], MediaAsset::KINDS, true)) {
            throw ValidationException::withMessages(['kind' => 'The selected media kind is invalid.']);
        }
        if (isset($values['transcription_status']) && ! in_array($values['transcription_status'], MediaAsset::TRANSCRIPTION_STATUSES, true)) {
            throw ValidationException::withMessages(['transcription_status' => 'The selected transcription status is invalid.']);
        }
        if (isset($values['rights_status']) && ! in_array($values['rights_status'], MediaAsset::RIGHTS_STATUSES, true)) {
            throw ValidationException::withMessages(['rights_status' => 'The selected rights status is invalid.']);
        }
        if (isset($values['byte_size']) && $values['byte_size'] < 0) {
            throw ValidationException::withMessages(['byte_size' => 'The byte size cannot be negative.']);
        }

        return MediaAsset::query()->create($values);
    }
}
