<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Liberu\Genealogy\GenealogyCore\Concerns\BelongsToTeam;

final class DnaMatch extends Model
{
    use BelongsToTeam;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'genealogy_dna_matches';

    protected $fillable = ['team_id', 'kit_id', 'external_id', 'display_name', 'predicted_relationship', 'confidence', 'total_cm', 'shared_segments', 'status', 'is_private', 'notes', 'metadata'];

    protected function casts(): array
    {
        return ['confidence' => 'integer', 'total_cm' => 'decimal:2', 'shared_segments' => 'integer', 'is_private' => 'boolean', 'metadata' => 'array'];
    }

    public function segments(): HasMany
    {
        return $this->hasMany(DnaSegment::class, 'match_id');
    }

    public function kit(): BelongsTo
    {
        return $this->belongsTo(DnaKit::class, 'kit_id');
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(DnaGroup::class, 'genealogy_dna_group_matches', 'match_id', 'group_id')->withTimestamps();
    }

    public function relationships(): HasMany
    {
        return $this->hasMany(DnaRelationship::class, 'match_id');
    }
}
