<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Timeline\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class TimelineEvent extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'timeline_events';

    protected $fillable = ['name', 'status', 'metadata'];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }
}
