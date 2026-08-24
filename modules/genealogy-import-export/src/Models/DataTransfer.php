<?php

declare(strict_types=1);

namespace Liberu\Genealogy\ImportExport\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Liberu\Genealogy\GenealogyCore\Concerns\BelongsToTeam;

final class DataTransfer extends Model
{
    use BelongsToTeam;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'genealogy_data_transfers';

    protected $fillable = ['team_id', 'name', 'format', 'direction', 'records_count', 'status', 'metadata'];

    protected function casts(): array
    {
        return ['metadata' => 'array', 'records_count' => 'integer'];
    }
}
