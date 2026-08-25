<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Liberu\Genealogy\GenealogyCore\Concerns\BelongsToTeam;

final class DnaNote extends Model
{
    use BelongsToTeam;
    use HasUuids;

    protected $table = 'genealogy_dna_notes';

    protected $fillable = ['team_id', 'noteable_type', 'noteable_id', 'body'];

    public function noteable(): MorphTo
    {
        return $this->morphTo();
    }
}
