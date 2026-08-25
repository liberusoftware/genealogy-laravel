<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Media\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\GenealogyCore\Concerns\BelongsToTeam;

final class MediaLink extends Model
{
    use BelongsToTeam;
    use HasUuids;

    protected $table = 'genealogy_media_links';

    protected $fillable = ['team_id', 'media_asset_id', 'linkable_type', 'linkable_id', 'role', 'metadata'];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }
}
