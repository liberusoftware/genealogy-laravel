<?php

declare(strict_types=1);

namespace Liberu\Genealogy\GenealogyCore\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Liberu\Genealogy\GenealogyCore\Concerns\BelongsToTeam;
use Liberu\Genealogy\GenealogyCore\Queries\TreeStatistics;
use LogicException;

class Tree extends Model
{
    public const STATUSES = ['draft', 'active', 'archived'];

    use BelongsToTeam;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'genealogy_trees';

    protected $fillable = [
        'team_id', 'name', 'status', 'description', 'root_person_id', 'is_public', 'metadata', 'user_id', 'identifier', 'terminology',
    ];

    protected $attributes = ['status' => 'draft', 'is_public' => false];

    protected static function booted(): void
    {
        self::creating(function (self $tree): void {
            if ($tree->user_id === null && function_exists('auth') && auth()->check()) {
                $tree->user_id = auth()->id();
            }
        });
    }

    protected function casts(): array
    {
        return ['is_public' => 'boolean', 'metadata' => 'array', 'terminology' => 'array'];
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where($query->getModel()->qualifyColumn('is_public'), true);
    }

    public function scopePrivate(Builder $query): Builder
    {
        return $query->where($query->getModel()->qualifyColumn('is_public'), false);
    }

    public function scopeOwnedBy(Builder $query, string|int $userId): Builder
    {
        return $query->where($query->getModel()->qualifyColumn('user_id'), (string) $userId);
    }

    public function isPrivate(): bool
    {
        return ! $this->is_public;
    }

    public function isOwnedBy(string|int $userId): bool
    {
        return (string) $this->user_id === (string) $userId;
    }

    public function rootPerson(): BelongsTo
    {
        return $this->belongsTo(
            (string) config('genealogy.person_model', 'Liberu\\Genealogy\\People\\Models\\Person'),
            'root_person_id',
        );
    }

    public function user(): BelongsTo
    {
        $model = config('genealogy.user_model') ?: config('auth.providers.users.model');

        if (! is_string($model) || $model === '') {
            throw new LogicException('Configure genealogy.user_model or an auth user provider model before loading tree owners.');
        }

        return $this->belongsTo($model, 'user_id');
    }

    /** @return array{total_people: int, total_ancestors: int, total_descendants: int, total_generations: int} */
    public function getStats(): array
    {
        return app(TreeStatistics::class)->for($this);
    }
}
