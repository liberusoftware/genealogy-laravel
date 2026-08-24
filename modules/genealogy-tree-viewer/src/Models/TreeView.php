<?php

declare(strict_types=1);

namespace Liberu\Genealogy\TreeViewer\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class TreeView extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'genealogy_trees';

    protected $fillable = ['name', 'status', 'metadata'];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }
}
