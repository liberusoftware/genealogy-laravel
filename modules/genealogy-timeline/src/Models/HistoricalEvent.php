<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Timeline\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

final class HistoricalEvent extends Model
{
    use HasUuids;

    protected $table = 'genealogy_historical_events';

    protected $fillable = ['title', 'description', 'event_date', 'year', 'month', 'day', 'place', 'country', 'latitude', 'longitude', 'source_url'];

    protected function casts(): array
    {
        return ['event_date' => 'date', 'year' => 'integer', 'month' => 'integer', 'day' => 'integer', 'latitude' => 'decimal:6', 'longitude' => 'decimal:6'];
    }

    public function scopeBetweenDates(Builder $query, string $start, string $end): Builder
    {
        return $query->whereBetween('event_date', [$start, $end]);
    }
}
