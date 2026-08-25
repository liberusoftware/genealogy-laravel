<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Concerns;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Liberu\Genealogy\GenealogyCore\Concerns\BelongsToTeam;

trait BelongsToEvidenceTeam
{
    use BelongsToTeam;
    use HasUuids;

    protected $guarded = ['id', 'team_id'];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }
}
