<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Gamification\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Liberu\Genealogy\GenealogyCore\Concerns\BelongsToTeam;

final class UserProgress extends Model
{
    use BelongsToTeam;
    use HasUuids;

    protected $table = 'genealogy_gamification_user_progress';

    protected $fillable = ['team_id', 'user_id', 'achievement_id', 'current_progress', 'target_progress', 'progress_data', 'started_at', 'last_updated_at'];

    protected function casts(): array
    {
        return ['current_progress' => 'integer', 'target_progress' => 'integer', 'progress_data' => 'array', 'started_at' => 'datetime', 'last_updated_at' => 'datetime'];
    }

    public function achievement(): BelongsTo
    {
        return $this->belongsTo(Achievement::class);
    }

    public function progressPercentage(): float
    {
        return $this->target_progress <= 0 ? 0.0 : min(100.0, ($this->current_progress / $this->target_progress) * 100);
    }

    public function isComplete(): bool
    {
        return $this->current_progress >= $this->target_progress;
    }

    public function incrementProgress(int $amount = 1, array $data = []): void
    {
        $this->setProgress($this->current_progress + $amount, $data);
    }

    public function setProgress(int $progress, array $data = []): void
    {
        $this->current_progress = max(0, $progress);
        $this->last_updated_at = now();
        if ($data !== []) {
            $this->progress_data = array_merge($this->progress_data ?? [], $data);
        }
        $this->save();
    }

    public function scopeIncomplete(Builder $query): Builder
    {
        return $query->whereColumn('current_progress', '<', 'target_progress');
    }
}
