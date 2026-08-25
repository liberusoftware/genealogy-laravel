<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Timeline\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Liberu\Genealogy\GenealogyCore\Concerns\BelongsToTeam;

final class TimelineEvent extends Model
{
    public const KINDS = ['life_event', 'family_event', 'historical_context', 'conflict'];

    public const DATE_PRECISIONS = ['exact', 'month', 'year', 'circa', 'before', 'after', 'range'];

    public const STATUSES = ['draft', 'active', 'completed'];

    use BelongsToTeam;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'timeline_events';

    protected $fillable = [
        'team_id', 'kind', 'name', 'subject_person_id', 'family_key', 'event_date', 'date_start', 'date_end',
        'date_precision', 'place_id', 'description', 'historical_context', 'conflict_group', 'confidence',
        'source_reference', 'is_private', 'status', 'metadata',
    ];

    protected function casts(): array
    {
        return ['event_date' => 'date', 'date_start' => 'date', 'date_end' => 'date', 'confidence' => 'integer', 'is_private' => 'boolean', 'metadata' => 'array'];
    }
}
