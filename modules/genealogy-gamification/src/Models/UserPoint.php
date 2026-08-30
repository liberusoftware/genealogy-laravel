<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Gamification\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\GenealogyCore\Concerns\BelongsToTeam;

final class UserPoint extends Model
{
    use BelongsToTeam;
    use HasUuids;

    protected $table = 'genealogy_gamification_points';

    protected $fillable = ['team_id', 'user_id', 'activity_type', 'points', 'description', 'metadata', 'related_id', 'related_type'];

    protected function casts(): array
    {
        return ['points' => 'integer', 'metadata' => 'array'];
    }
}
