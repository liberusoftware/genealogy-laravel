<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Discovery\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Liberu\Genealogy\GenealogyCore\Concerns\BelongsToTeam;

final class DiscoveryMatch extends Model
{
    public const KINDS = ['hint', 'duplicate', 'relationship_path'];

    public const STATUSES = ['draft', 'active', 'dismissed', 'completed'];

    use BelongsToTeam;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'genealogy_discovery_matches';

    protected $fillable = [
        'team_id', 'kind', 'name', 'subject_id', 'related_id', 'confidence', 'rationale', 'source_type',
        'detected_at', 'reviewed_at', 'status', 'metadata',
    ];

    protected function casts(): array
    {
        return ['confidence' => 'integer', 'detected_at' => 'datetime', 'reviewed_at' => 'datetime', 'metadata' => 'array'];
    }
}
