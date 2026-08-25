<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Liberu\Genealogy\Evidence\Concerns\BelongsToEvidenceTeam;

final class Extract extends Model
{
    use BelongsToEvidenceTeam;
    use SoftDeletes;

    protected $table = 'genealogy_evidence_extracts';

    protected $fillable = ['citation_id', 'content', 'transcription', 'page', 'metadata'];

    public function citation(): BelongsTo
    {
        return $this->belongsTo(Citation::class);
    }

    public function assertions(): HasMany
    {
        return $this->hasMany(Assertion::class);
    }
}
