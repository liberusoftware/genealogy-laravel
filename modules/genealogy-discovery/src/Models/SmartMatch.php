<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Discovery\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Liberu\Genealogy\GenealogyCore\Concerns\BelongsToTeam;

final class SmartMatch extends Model
{
    use BelongsToTeam;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'genealogy_smart_matches';

    protected $fillable = ['team_id', 'user_id', 'person_id', 'external_tree_id', 'external_person_id', 'match_source', 'record_category', 'match_data', 'search_criteria', 'confidence_score', 'status', 'reviewed_at'];

    protected function casts(): array
    {
        return ['match_data' => 'array', 'search_criteria' => 'array', 'confidence_score' => 'integer', 'reviewed_at' => 'datetime'];
    }
}
