<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Liberu\Genealogy\Evidence\Concerns\BelongsToEvidenceTeam;
use Liberu\Genealogy\People\Models\Person;

final class CitationLink extends Model
{
    public const GROUPS = ['indi', 'indi_name', 'indi_even', 'indi_asso', 'indi_lds'];

    use BelongsToEvidenceTeam;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'genealogy_evidence_citation_links';

    protected $fillable = ['team_id', 'citation_id', 'subject_person_id', 'group', 'page', 'quality', 'text', 'metadata'];

    public function citation(): BelongsTo
    {
        return $this->belongsTo(Citation::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'subject_person_id');
    }

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    public function qualityLabel(): string
    {
        return match ((string) $this->quality) {
            '0' => 'Unreliable', '1' => 'Questionable', '2' => 'Secondary evidence', '3' => 'Primary evidence',
            '' => 'Unrated', default => (string) $this->quality,
        };
    }
}
