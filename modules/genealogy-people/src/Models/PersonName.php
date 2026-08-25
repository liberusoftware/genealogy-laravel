<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\GenealogyCore\Concerns\BelongsToTeam;

final class PersonName extends Model
{
    use BelongsToTeam;
    use HasUuids;

    protected $table = 'genealogy_person_names';

    protected $fillable = ['team_id', 'person_id', 'type', 'given_name', 'family_name', 'prefix', 'suffix', 'is_preferred', 'metadata'];

    protected function casts(): array
    {
        return ['is_preferred' => 'boolean', 'metadata' => 'array'];
    }
}
