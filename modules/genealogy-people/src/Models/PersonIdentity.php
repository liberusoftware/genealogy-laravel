<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\GenealogyCore\Concerns\BelongsToTeam;

final class PersonIdentity extends Model
{
    use BelongsToTeam;
    use HasUuids;

    protected $table = 'genealogy_person_identities';

    protected $fillable = ['team_id', 'person_id', 'type', 'value', 'label', 'is_verified', 'metadata'];

    protected function casts(): array
    {
        return ['is_verified' => 'boolean', 'metadata' => 'array'];
    }
}
