<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Gamification\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Achievement extends Model
{
    use HasUuids;

    protected $table = 'genealogy_gamification_achievements';

    protected $fillable = ['key', 'name', 'description', 'category', 'points', 'requirements', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['points' => 'integer', 'requirements' => 'array', 'is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    public function userProgress(): HasMany
    {
        return $this->hasMany(UserProgress::class, 'achievement_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
