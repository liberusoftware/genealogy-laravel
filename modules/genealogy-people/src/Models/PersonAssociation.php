<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Liberu\Genealogy\GenealogyCore\Concerns\BelongsToTeam;

final class PersonAssociation extends Model
{
    use BelongsToTeam;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'genealogy_person_associations';

    protected $fillable = ['team_id', 'person_id', 'associated_person_id', 'associated_external_id', 'relationship', 'description', 'metadata'];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function associatedPerson(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'associated_person_id');
    }

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    public function isResolved(): bool
    {
        return $this->associated_person_id !== null;
    }
}
