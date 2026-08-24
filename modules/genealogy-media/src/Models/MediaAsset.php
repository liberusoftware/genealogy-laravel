<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Media\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class MediaAsset extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'genealogy_media_assets';

    protected $fillable = ['name', 'status', 'metadata'];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }
}
