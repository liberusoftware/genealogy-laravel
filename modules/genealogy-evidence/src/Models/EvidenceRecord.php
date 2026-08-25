<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Liberu\Genealogy\GenealogyCore\Concerns\BelongsToTeam;
use Liberu\Genealogy\People\Models\Person;

final class EvidenceRecord extends Model
{
    public const KINDS = ['source', 'repository', 'citation', 'extract', 'assertion', 'proof_conclusion'];

    public const STATUSES = ['draft', 'active', 'completed', 'archived'];

    use BelongsToTeam;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'evidence_records';

    protected $fillable = [
        'team_id', 'name', 'kind', 'repository', 'citation', 'extract', 'assertion',
        'proof_conclusion', 'confidence', 'source_url', 'event_date', 'subject_person_id',
        'reviewed_at', 'status', 'metadata',
    ];

    protected function casts(): array
    {
        return ['confidence' => 'integer', 'event_date' => 'date', 'reviewed_at' => 'datetime', 'metadata' => 'array'];
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'subject_person_id');
    }

    public function scopeHighConfidence(Builder $query): Builder
    {
        return $query->where('confidence', '>=', 75);
    }

    public function isHighConfidence(): bool
    {
        return $this->confidence >= 75;
    }

    public function hasProofConclusion(): bool
    {
        return filled($this->proof_conclusion);
    }
}
