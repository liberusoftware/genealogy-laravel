<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Media\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Liberu\Genealogy\GenealogyCore\Concerns\BelongsToTeam;

final class MediaAsset extends Model
{
    public const KINDS = ['document', 'photograph', 'audio', 'video'];

    public const TRANSCRIPTION_STATUSES = ['not_started', 'pending', 'in_progress', 'completed', 'failed'];

    public const RIGHTS_STATUSES = ['unknown', 'owned', 'licensed', 'public_domain', 'restricted', 'orphaned'];

    use BelongsToTeam;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'genealogy_media_assets';

    protected $fillable = [
        'team_id', 'kind', 'name', 'storage_disk', 'storage_path', 'mime_type', 'byte_size', 'checksum',
        'captured_at', 'captured_place_id', 'transcription', 'transcription_status', 'transcription_language',
        'rights_holder', 'rights_status', 'license_url', 'rights_expires_at', 'is_public', 'preservation_metadata',
        'status', 'metadata',
    ];

    protected function casts(): array
    {
        return ['captured_at' => 'datetime', 'rights_expires_at' => 'date', 'is_public' => 'boolean', 'byte_size' => 'integer', 'preservation_metadata' => 'array', 'metadata' => 'array'];
    }

    public function links(): HasMany
    {
        return $this->hasMany(MediaLink::class, 'media_asset_id');
    }

    public function faceTags(): HasMany
    {
        return $this->hasMany(MediaFaceTag::class, 'media_asset_id');
    }

    public function transcriptionCorrections(): HasMany
    {
        return $this->hasMany(MediaTranscriptionCorrection::class, 'media_asset_id');
    }
}
