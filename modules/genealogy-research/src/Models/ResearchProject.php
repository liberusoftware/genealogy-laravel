<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Research\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Liberu\Genealogy\GenealogyCore\Concerns\BelongsToTeam;

final class ResearchProject extends Model
{
    public const STATUSES = ['draft', 'active', 'completed', 'archived'];

    use BelongsToTeam;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'research_projects';

    protected $fillable = ['team_id', 'name', 'status', 'metadata'];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    public function entries(): HasMany
    {
        return $this->hasMany(ResearchEntry::class, 'research_project_id');
    }
}
