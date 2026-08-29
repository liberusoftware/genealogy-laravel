<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Media\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Liberu\Genealogy\GenealogyCore\Concerns\BelongsToTeam;

final class MediaFaceTag extends Model
{
    public const STATUSES = ['pending', 'confirmed', 'rejected'];

    use BelongsToTeam;
    use HasUuids;

    protected $table = 'genealogy_media_face_tags';

    protected $fillable = ['team_id', 'media_asset_id', 'person_id', 'confidence', 'bounding_box', 'status', 'confirmed_by', 'confirmed_at', 'metadata'];

    protected function casts(): array
    {
        return ['confidence' => 'decimal:2', 'bounding_box' => 'array', 'confirmed_at' => 'datetime', 'metadata' => 'array'];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'media_asset_id');
    }
}
