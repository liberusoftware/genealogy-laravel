<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Media\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Liberu\Genealogy\GenealogyCore\Concerns\BelongsToTeam;

final class MediaTranscriptionCorrection extends Model
{
    use BelongsToTeam;
    use HasUuids;

    protected $table = 'genealogy_media_transcription_corrections';

    protected $fillable = ['team_id', 'media_asset_id', 'original_text', 'corrected_text', 'actor_id', 'metadata'];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'media_asset_id');
    }
}
