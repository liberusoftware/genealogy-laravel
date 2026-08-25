<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Liberu\Genealogy\GenealogyCore\Concerns\BelongsToTeam;

final class DnaProvider extends Model
{
    public const STATUSES = ['active', 'inactive'];

    use BelongsToTeam;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'genealogy_dna_providers';

    protected $fillable = ['team_id', 'name', 'slug', 'status', 'website', 'metadata'];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    public function kits(): HasMany
    {
        return $this->hasMany(DnaKit::class, 'provider_id');
    }
}
