<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Liberu\Genealogy\Evidence\Concerns\BelongsToEvidenceTeam;

final class Citation extends Model
{
    use BelongsToEvidenceTeam;
    use SoftDeletes;

    protected $table = 'genealogy_evidence_citations';

    protected $fillable = ['source_id', 'repository_id', 'title', 'volume', 'page', 'text', 'confidence', 'event_date', 'metadata'];

    protected function casts(): array
    {
        return ['confidence' => 'integer', 'event_date' => 'date', 'metadata' => 'array'];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    public function repository(): BelongsTo
    {
        return $this->belongsTo(Repository::class);
    }

    public function extracts(): HasMany
    {
        return $this->hasMany(Extract::class);
    }

    public function assertions(): HasMany
    {
        return $this->hasMany(Assertion::class);
    }
}
