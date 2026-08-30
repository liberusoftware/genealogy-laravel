<?php

declare(strict_types=1);

namespace Liberu\Genealogy\ImportExport\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Liberu\Genealogy\GenealogyCore\Concerns\BelongsToTeam;

final class DataTransfer extends Model
{
    public const FORMATS = ['gedcom', 'gedcom-7', 'gedcom-x', 'gramps-xml'];

    public const DIRECTIONS = ['import', 'export'];

    public const STATUSES = ['draft', 'active', 'completed', 'failed', 'rolled_back'];

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
