<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Relationships\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Liberu\Genealogy\GenealogyCore\Concerns\BelongsToTeam;
use Liberu\Genealogy\People\Models\Person;

final class Relationship extends Model
{
    public const TYPES = ['parent', 'partner', 'household', 'adoption', 'guardianship', 'uncertain'];

    use BelongsToTeam;
    use HasUuids;

    protected $table = 'genealogy_relationships';

    protected $fillable = ['team_id', 'person_id', 'related_person_id', 'type', 'confidence', 'metadata'];

    protected function casts(): array
    {
        return ['confidence' => 'integer', 'metadata' => 'array'];
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function relatedPerson(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'related_person_id');
    }
}
