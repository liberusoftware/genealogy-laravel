<?php

namespace Liberu\Foundation\Search\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * The query scope `SearchService` calls on the configured user model.
 *
 * Without this the package was incomplete: `searchUsers()` calls `->search()`,
 * but the scope only existed on the host's own `App\Models\User`. Any
 * application installing this package and pointing `search.models.user` at its
 * own model got `Call to undefined method` on the first search.
 *
 * @template TModel of \Illuminate\Database\Eloquent\Model
 */
trait Searchable
{
    /**
     * Match the term against the columns this model considers searchable.
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        // Grouped, so an added filter cannot be swallowed by the ORs.
        $columns = $this->searchableColumns();

        $query->where(function (Builder $q) use ($search, $columns): void {
            foreach ($columns as $index => $column) {
                $index === 0
                    ? $q->where($column, 'like', "%{$search}%")
                    : $q->orWhere($column, 'like', "%{$search}%");
            }
        });

        // Stable relevance ordering prevents a broad contains match (for example,
        // "Rosemarie Johns") from outranking a direct prefix match ("John Doe")
        // when records share the same timestamp. The column list is supplied by
        // the model, while the user input remains parameter-bound.
        if ($columns !== []) {
            $rank = implode(' ', array_map(
                static fn (int $index, string $column): string => "WHEN {$column} LIKE ? THEN {$index}",
                array_keys($columns),
                $columns,
            ));

            $query->orderByRaw("CASE {$rank} ELSE 99 END", array_fill(0, count($columns), "{$search}%"));
        }

        return $query;
    }

    /**
     * Override to search different columns.
     *
     * @return list<string>
     */
    public function searchableColumns(): array
    {
        return ['name', 'email'];
    }
}
