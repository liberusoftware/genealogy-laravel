<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Gamification\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Liberu\Genealogy\GenealogyCore\Concerns\BelongsToTeam;

final class UserAchievement extends Model
{
    use BelongsToTeam;
    use HasUuids;

    protected $table = 'genealogy_gamification_user_achievements';

    protected $fillable = ['team_id', 'user_id', 'achievement_id', 'unlocked_at', 'progress_data'];

    protected function casts(): array
    {
        return ['unlocked_at' => 'datetime', 'progress_data' => 'array'];
    }

    public function achievement(): BelongsTo
    {
        return $this->belongsTo(Achievement::class);
    }
}
