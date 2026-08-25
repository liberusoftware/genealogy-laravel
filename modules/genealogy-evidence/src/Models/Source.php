<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Liberu\Genealogy\Evidence\Concerns\BelongsToEvidenceTeam;

final class Source extends Model
{
    use BelongsToEvidenceTeam;
    use SoftDeletes;

    protected $table = 'genealogy_evidence_sources';

    protected $fillable = ['name', 'description', 'url', 'archive_metadata', 'record_type', 'is_active', 'metadata'];

    protected function casts(): array
    {
        return ['archive_metadata' => 'array', 'is_active' => 'boolean', 'metadata' => 'array'];
    }

    public function repositories(): HasMany
    {
        return $this->hasMany(Repository::class);
    }

    public function citations(): HasMany
    {
        return $this->hasMany(Citation::class);
    }
}
