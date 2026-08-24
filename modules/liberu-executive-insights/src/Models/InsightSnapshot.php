<?php

declare(strict_types=1);

namespace Liberu\Platform\ExecutiveInsights\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class InsightSnapshot extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'liberu_insight_snapshots';

    protected $fillable = ['name', 'status', 'metadata'];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }
}
