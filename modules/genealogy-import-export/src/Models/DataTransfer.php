<?php

declare(strict_types=1);

namespace Liberu\Genealogy\ImportExport\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class DataTransfer extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'genealogy_data_transfers';

    protected $fillable = ['name', 'status', 'metadata'];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }
}
