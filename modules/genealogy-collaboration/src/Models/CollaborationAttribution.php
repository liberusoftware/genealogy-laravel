<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\GenealogyCore\Concerns\BelongsToTeam;

final class CollaborationAttribution extends Model
{
    public $timestamps = false;

    use BelongsToTeam;
    use HasUuids;

    protected $table = 'genealogy_collaboration_attributions';

    protected $fillable = ['team_id', 'actor_id', 'attributable_type', 'attributable_id', 'action', 'metadata', 'created_at'];

    protected function casts(): array
    {
        return ['metadata' => 'array', 'created_at' => 'datetime'];
    }
}
