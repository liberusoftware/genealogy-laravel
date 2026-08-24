<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Discovery\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class DiscoveryMatch extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'genealogy_discovery_matches';

    protected $fillable = ['name', 'status', 'metadata'];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }
}
