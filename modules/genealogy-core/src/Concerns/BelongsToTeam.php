<?php

declare(strict_types=1);

namespace Liberu\Genealogy\GenealogyCore\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use LogicException;

trait BelongsToTeam
{
    protected static function bootBelongsToTeam(): void
    {
        static::addGlobalScope('team', function (Builder $query): void {
            $model = $query->getModel();

            if (! $model->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), 'team_id')) {
                return;
            }

            $context = app(TeamContext::class)->current();

            if ($context === null) {
                // Guests may only see explicitly public records. This keeps
                // private tenant data concealed while allowing public trees
                // to be shared without inventing a tenant context.
                if ($model->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), 'is_public')) {
                    $query->where($model->qualifyColumn('is_public'), true);
                } else {
                    $query->whereRaw('1 = 0');
                }

                return;
            }

            $query->where($model->qualifyColumn('team_id'), $context);
        });

        static::creating(function (Model $model): void {
            if (! $model->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), 'team_id')) {
                return;
            }

            $context = app(TeamContext::class)->current();

            if ($context === null) {
                throw new LogicException('A team context is required to create a genealogy record.');
            }

            $model->setAttribute('team_id', $context);
        });
    }

    public function team(): BelongsTo
    {
        $teamModel = (string) config('genealogy.team_model', 'Liberu\\Foundation\\Organizations\\Models\\Team');

        return $this->belongsTo($teamModel, 'team_id');
    }

    public function scopeForTeam(Builder $query, string|int $teamId): Builder
    {
        return $query->withoutGlobalScope('team')->where($query->getModel()->qualifyColumn('team_id'), (string) $teamId);
    }
}
