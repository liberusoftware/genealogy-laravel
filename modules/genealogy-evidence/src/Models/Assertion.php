<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Liberu\Genealogy\Evidence\Concerns\BelongsToEvidenceTeam;
use Liberu\Genealogy\People\Models\Person;

final class Assertion extends Model
{
    use BelongsToEvidenceTeam;
    use SoftDeletes;

    protected $table = 'genealogy_evidence_assertions';

    protected $fillable = ['subject_person_id', 'citation_id', 'extract_id', 'statement', 'confidence', 'status', 'metadata'];

    protected function casts(): array
    {
        return ['confidence' => 'integer', 'metadata' => 'array'];
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'subject_person_id');
    }

    public function citation(): BelongsTo
    {
        return $this->belongsTo(Citation::class);
    }

    public function extract(): BelongsTo
    {
        return $this->belongsTo(Extract::class);
    }

    public function proofConclusion(): HasOne
    {
        return $this->hasOne(ProofConclusion::class);
    }
}
