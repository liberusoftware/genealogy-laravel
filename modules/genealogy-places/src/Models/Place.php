<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Places\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Place extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'genealogy_places';

    protected $fillable = ['name', 'status', 'metadata'];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }
}
