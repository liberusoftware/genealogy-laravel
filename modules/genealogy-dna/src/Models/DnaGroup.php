<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Liberu\Genealogy\GenealogyCore\Concerns\BelongsToTeam;

final class DnaGroup extends Model
{
    use BelongsToTeam;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'genealogy_dna_groups';

    protected $fillable = ['team_id', 'name', 'description', 'status', 'metadata'];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    public function matches(): BelongsToMany
    {
        return $this->belongsToMany(DnaMatch::class, 'genealogy_dna_group_matches', 'group_id', 'match_id')->withTimestamps();
    }
}
