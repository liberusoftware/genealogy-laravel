<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Reports\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Liberu\Genealogy\GenealogyCore\Concerns\BelongsToTeam;

final class GenealogyReport extends Model
{
    public const TYPES = ['family_group', 'pedigree', 'descendants', 'timeline', 'research', 'sources', 'chart'];

    public const STATUSES = ['draft', 'active', 'queued', 'running', 'completed', 'failed', 'archived'];

    use BelongsToTeam;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'genealogy_reports';

    protected $fillable = ['team_id', 'name', 'type', 'status', 'metadata', 'generated_output', 'generated_at'];

    protected function casts(): array
    {
        return ['metadata' => 'array', 'generated_output' => 'array', 'generated_at' => 'datetime'];
    }
}
