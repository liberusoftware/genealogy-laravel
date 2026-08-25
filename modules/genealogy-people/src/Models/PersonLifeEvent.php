<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\GenealogyCore\Concerns\BelongsToTeam;

final class PersonLifeEvent extends Model
{
    use BelongsToTeam;
    use HasUuids;

    protected $table = 'genealogy_person_life_events';

    protected $fillable = ['team_id', 'person_id', 'type', 'date', 'place', 'description', 'metadata'];

    protected function casts(): array
    {
        return ['date' => 'date', 'metadata' => 'array'];
    }
}
