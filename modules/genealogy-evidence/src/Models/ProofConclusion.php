<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Liberu\Genealogy\Evidence\Concerns\BelongsToEvidenceTeam;

final class ProofConclusion extends Model
{
    use BelongsToEvidenceTeam;
    use SoftDeletes;

    protected $table = 'genealogy_evidence_proof_conclusions';

    protected $fillable = ['assertion_id', 'conclusion', 'confidence', 'status', 'metadata'];

    protected function casts(): array
    {
        return ['confidence' => 'integer', 'metadata' => 'array'];
    }

    public function assertion(): BelongsTo
    {
        return $this->belongsTo(Assertion::class);
    }
}
