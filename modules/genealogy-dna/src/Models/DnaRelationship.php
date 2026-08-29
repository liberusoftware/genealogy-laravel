<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Liberu\Genealogy\GenealogyCore\Concerns\BelongsToTeam;

final class DnaRelationship extends Model
{
    public const STATUSES = ['proposed', 'confirmed', 'rejected'];

    use BelongsToTeam;
    use HasUuids;

    protected $table = 'genealogy_dna_relationships';

    protected $fillable = ['team_id', 'match_id', 'person_id', 'relationship_type', 'confidence', 'status', 'rationale', 'metadata'];

    protected function casts(): array
    {
        return ['confidence' => 'integer', 'metadata' => 'array'];
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(DnaMatch::class, 'match_id');
    }
}
