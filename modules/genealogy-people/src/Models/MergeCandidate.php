<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Liberu\Genealogy\GenealogyCore\Concerns\BelongsToTeam;

final class MergeCandidate extends Model
{
    use BelongsToTeam;
    use HasUuids;

    protected $table = 'genealogy_merge_candidates';

    protected $fillable = ['team_id', 'person_id', 'candidate_person_id', 'status', 'score', 'reason', 'metadata', 'reviewed_at'];

    protected function casts(): array
    {
        return ['score' => 'decimal:4', 'metadata' => 'array', 'reviewed_at' => 'datetime'];
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function candidatePerson(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'candidate_person_id');
    }
}
